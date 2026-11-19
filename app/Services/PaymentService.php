<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Enums\PaymentStatus;
use App\Events\PaymentVerified;
use App\Models\Payment;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PaymentService
{
    public function __construct(private OrderService $orders) {}

    public function verify(Payment $payment, ?string $note = null): void
    {
        DB::transaction(function () use ($payment, $note) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === PaymentStatus::Verified) {
                throw new RuntimeException('This payment is already verified.');
            }
            if ($payment->status === PaymentStatus::Refunded) {
                throw new RuntimeException('A refunded payment cannot be verified again.');
            }

            $payment->forceFill([
                'status' => PaymentStatus::Verified,
                'verified_at' => now(),
                'verified_by' => auth('admin')->id(),
                'rejection_reason' => null,
            ])->save();

            $order = $payment->order()->lockForUpdate()->first();
            // Any other pending payment on this order is now superseded.
            $order->payments()->where('id', '!=', $payment->id)->where('status', PaymentStatus::Pending)
                ->update(['status' => PaymentStatus::Rejected, 'rejection_reason' => 'Superseded by verified payment']);
            $order->payment_status = PaymentStatus::Verified;
            $order->save();

            if (in_array($order->status, [OrderStatus::Draft, OrderStatus::Submitted, OrderStatus::PaymentPending], true)) {
                $this->orders->transition($order, OrderStatus::PaymentVerified, $note ?: 'Payment verified (TrxID '.$payment->transaction_id.')');
            }

            ActivityLogger::log('payment.verified', $payment, "{$order->order_number} · ৳{$payment->amount} · {$payment->transaction_id}");
        });

        PaymentVerified::dispatch($payment->fresh());
    }

    public function reject(Payment $payment, string $reason): void
    {
        DB::transaction(function () use ($payment, $reason) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status === PaymentStatus::Refunded) {
                throw new RuntimeException('This payment was refunded.');
            }
            $payment->forceFill(['status' => PaymentStatus::Rejected, 'rejection_reason' => $reason, 'verified_at' => null])->save();

            $order = $payment->order;
            $hasVerified = $order->payments()->where('status', PaymentStatus::Verified)->exists();
            $order->payment_status = $hasVerified ? PaymentStatus::Verified : PaymentStatus::Rejected;
            $order->save();

            if (! $hasVerified && $order->status === OrderStatus::PaymentVerified) {
                $this->orders->transition($order, OrderStatus::PaymentPending, 'Payment rejected: '.$reason);
            } else {
                $order->histories()->create(['from_status' => $order->status->value, 'to_status' => $order->status->value, 'note' => 'Payment rejected: '.mb_substr($reason, 0, 200), 'admin_id' => auth('admin')->id()]);
            }

            ActivityLogger::log('payment.rejected', $payment, "{$order->order_number} · {$payment->transaction_id}", ['reason' => $reason]);
        });
    }

    public function refund(Payment $payment, ?string $note = null): void
    {
        DB::transaction(function () use ($payment, $note) {
            $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($payment->status !== PaymentStatus::Verified) {
                throw new RuntimeException('Only a verified payment can be marked as refunded.');
            }
            $payment->forceFill(['status' => PaymentStatus::Refunded, 'refunded_at' => now(), 'refund_note' => $note])->save();

            $order = $payment->order;
            $order->payment_status = PaymentStatus::Refunded;
            $order->save();
            $this->orders->transition($order, OrderStatus::Refunded, 'Refunded'.($note ? ': '.$note : ''));

            if ($order->page && $order->page->status === PageStatus::Active) {
                $order->page->update(['status' => PageStatus::Suspended]);
            }

            ActivityLogger::log('payment.refunded', $payment, "{$order->order_number} · ৳{$payment->amount}", ['note' => $note]);
        });
    }
}
