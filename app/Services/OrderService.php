<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Events\OrderSubmitted;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Package;
use App\Models\PageMedia;
use App\Models\Payment;
use App\Models\Template;
use App\Support\ActivityLogger;
use App\Support\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private PricingService $pricing,
        private ImageService $images,
        private Settings $settings,
    ) {}

    /**
     * @param  array  $data  validated: template, package, coupon_code, customer{}, content{}, media_ids[], payment{}
     */
    public function submit(array $data, Template $template, Package $package, array $content, string $draftToken, ?string $ip): Order
    {
        $order = DB::transaction(function () use ($data, $template, $package, $content, $draftToken, $ip) {
            $phone = $data['customer_phone'];
            $code = strtoupper(trim((string) ($data['coupon_code'] ?? '')));

            // Lock the coupon row so two simultaneous orders can't exceed its usage limit.
            $coupon = $code !== '' ? Coupon::where('code', $code)->lockForUpdate()->first() : null;
            $quote = $this->pricing->quote($package, $code, $phone, $coupon);
            if ($quote['error']) {
                throw ValidationException::withMessages(['coupon_code' => $quote['error']]);
            }

            $order = Order::create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $phone,
                'customer_email' => $data['customer_email'] ?? null,
                'category_id' => $template->category_id,
                'template_id' => $template->id,
                'package_id' => $package->id,
                'coupon_id' => $quote['coupon']?->id,
                'coupon_code' => $quote['coupon']?->code,
                'snapshot' => [
                    'category' => $template->category->name,
                    'template' => $template->name,
                    'package' => $package->name,
                    'duration_value' => $package->duration_value,
                    'duration_unit' => $package->duration_unit,
                    'coupon' => $quote['coupon'] ? ['code' => $quote['coupon']->code, 'type' => $quote['coupon']->type, 'value' => (string) $quote['coupon']->value] : null,
                ],
                'occasion' => $data['occasion'] ?? null,
                'original_price' => $quote['original'],
                'discount_amount' => $quote['discount'],
                'final_price' => $quote['final'],
                'status' => OrderStatus::Submitted,
                'payment_status' => PaymentStatus::Pending,
                'content' => $content,
                'desired_slug' => $data['desired_slug'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'ip_address' => $ip,
                'submitted_at' => now(),
            ]);

            $order->forceFill(['order_number' => 'GB-'.now()->format('Ymd').'-'.(1000 + $order->id)])->save();

            Payment::create([
                'order_id' => $order->id,
                'method' => 'bkash',
                'amount' => $data['payment_amount'],
                'expected_amount' => $quote['final'],
                'receiver_number' => $this->settings->bkashNumber(),
                'sender_number' => $data['payment_sender'],
                'transaction_id' => $data['payment_trx'],
                'status' => PaymentStatus::Pending,
            ]);

            if ($quote['coupon']) {
                CouponUsage::create([
                    'coupon_id' => $quote['coupon']->id, 'order_id' => $order->id,
                    'phone' => $phone, 'discount_amount' => $quote['discount'],
                ]);
                $quote['coupon']->increment('used_count');
            }

            // Claim this browser's draft uploads (only the ones it actually listed).
            $ids = collect($data['media'] ?? [])->flatten()->map(fn ($v) => (int) $v)->filter()->unique()->all();
            if ($ids) {
                $sortById = array_flip($ids);
                PageMedia::where('draft_token', $draftToken)->whereNull('order_id')->whereIn('id', $ids)->get()
                    ->each(function (PageMedia $m) use ($order, $sortById) {
                        $m->forceFill(['order_id' => $order->id, 'draft_token' => null, 'sort_order' => $sortById[$m->id] ?? 0])->save();
                    });
            }

            $this->recordStatus($order, null, OrderStatus::Submitted, 'Order submitted by customer');
            $this->transition($order, OrderStatus::PaymentPending, 'Waiting for bKash verification');

            return $order;
        });

        $this->images->moveToOrderFolder($order);
        OrderSubmitted::dispatch($order);

        return $order;
    }

    public function transition(Order $order, OrderStatus $to, ?string $note = null, bool $log = false): void
    {
        $from = $order->status;
        if ($from === $to) {
            return;
        }
        $order->status = $to;
        if ($to === OrderStatus::Published && ! $order->delivered_at) {
            $order->delivered_at = now();
        }
        $order->save();
        $this->recordStatus($order, $from, $to, $note);

        if ($log) {
            ActivityLogger::log('order.status_changed', $order, "{$order->order_number}: ".($from?->label() ?? '—').' → '.$to->label(), ['note' => $note]);
        }
    }

    private function recordStatus(Order $order, ?OrderStatus $from, OrderStatus $to, ?string $note): void
    {
        $order->histories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'note' => $note ? mb_substr($note, 0, 250) : null,
            'admin_id' => auth('admin')->id(),
        ]);
    }

    /** Frees a coupon slot when an order is cancelled. */
    public function releaseCoupon(Order $order): void
    {
        $usage = CouponUsage::where('order_id', $order->id)->first();
        if ($usage) {
            Coupon::whereKey($usage->coupon_id)->where('used_count', '>', 0)->decrement('used_count');
            $usage->delete();
        }
    }

    /** Re-price after an admin changes package or manual discount. */
    public function reprice(Order $order, Package $package, float $manualDiscount): void
    {
        $original = round((float) $package->price, 2);
        $discount = (float) $order->discount_amount;
        if ($order->coupon) {
            $coupon = $order->coupon;
            $discount = $coupon->type === 'percent' ? round($original * (float) $coupon->value / 100, 2) : (float) $coupon->value;
            if ($coupon->max_discount !== null) {
                $discount = min($discount, (float) $coupon->max_discount);
            }
        }
        $discount = max(0, min($discount, $original));
        $manualDiscount = max(0, min($manualDiscount, $original - $discount));

        $snapshot = $order->snapshot ?? [];
        $snapshot['package'] = $package->name;
        $snapshot['duration_value'] = $package->duration_value;
        $snapshot['duration_unit'] = $package->duration_unit;

        $order->fill([
            'package_id' => $package->id,
            'original_price' => $original,
            'discount_amount' => $discount,
            'manual_discount' => $manualDiscount,
            'final_price' => round($original - $discount - $manualDiscount, 2),
            'snapshot' => $snapshot,
        ])->save();

        CouponUsage::where('order_id', $order->id)->update(['discount_amount' => $discount]);
    }
}
