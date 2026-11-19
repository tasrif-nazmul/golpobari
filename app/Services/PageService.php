<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Enums\PageVisibility;
use App\Events\PagePublished;
use App\Models\Order;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageMedia;
use App\Support\ActivityLogger;
use App\Support\Settings;
use App\Support\SlugRules;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PageService
{
    public function __construct(private OrderService $orders, private Settings $settings) {}

    public function createFromOrder(Order $order): Page
    {
        if ($order->page) {
            return $order->page;
        }
        if (! $order->template_id) {
            throw new RuntimeException('This order has no template. Choose a template first.');
        }

        return DB::transaction(function () use ($order) {
            $slugBase = $order->desired_slug ?: ($this->guessName($order) ?: $order->customer_name);
            $title = $this->guessTitle($order);
            $packageBranding = $order->package ? $order->package->showsBranding() : true;

            $page = Page::create([
                'order_id' => $order->id,
                'template_id' => $order->template_id,
                'slug' => SlugRules::suggest($slugBase),
                'title' => $title,
                'status' => PageStatus::Draft,
                'visibility' => PageVisibility::Public,
                'content' => $order->content ?? [],
                'seo' => [
                    'title' => $title,
                    'description' => Str::limit($this->guessDescription($order), 155),
                ],
                'show_branding' => $packageBranding,
            ]);

            PageMedia::where('order_id', $order->id)->update(['page_id' => $page->id]);

            if (in_array($order->status, [OrderStatus::PaymentVerified, OrderStatus::InReview], true)) {
                $this->orders->transition($order, OrderStatus::InProduction, 'Page draft created');
            }
            ActivityLogger::log('page.created', $page, "/{$page->slug} from {$order->order_number}");

            return $page;
        });
    }

    public function markReady(Page $page): void
    {
        $page->update(['status' => PageStatus::Pending]);
        if ($page->order && in_array($page->order->status, [OrderStatus::PaymentVerified, OrderStatus::InReview, OrderStatus::InProduction], true)) {
            $this->orders->transition($page->order, OrderStatus::Ready, 'Page ready for publishing');
        }
        ActivityLogger::log('page.ready', $page, '/'.$page->slug);
    }

    public function publish(Page $page): void
    {
        DB::transaction(function () use ($page) {
            $order = $page->order;
            if ($order && ! in_array($order->payment_status?->value, ['verified'], true)) {
                throw new RuntimeException('Verify the payment before publishing this page.');
            }

            $now = now();
            if (! $page->expires_at || $page->expires_at->isPast()) {
                $package = $order?->package;
                $page->expires_at = $package ? $package->addDurationTo($now) : $this->durationFromSnapshot($order, $now);
            }
            $page->published_at ??= $now;
            $page->status = PageStatus::Active;
            $page->save();

            if ($order && $order->status !== OrderStatus::Published) {
                $this->orders->transition($order, OrderStatus::Published, 'Published at /'.$page->slug);
            }
            ActivityLogger::log('page.published', $page, $page->url().' until '.$page->expires_at?->format('d M Y'));
        });

        PagePublished::dispatch($page);
    }

    public function unpublish(Page $page): void
    {
        $page->update(['status' => PageStatus::Pending]);
        if ($page->order && $page->order->status === OrderStatus::Published) {
            $this->orders->transition($page->order, OrderStatus::Ready, 'Page unpublished');
        }
        ActivityLogger::log('page.unpublished', $page, '/'.$page->slug);
    }

    public function setStatus(Page $page, PageStatus $status): void
    {
        $old = $page->status;
        $page->update(['status' => $status]);
        ActivityLogger::log('page.status', $page, "/{$page->slug}: {$old->value} → {$status->value}");
    }

    /**
     * Extend by a package's duration (from the later of now / current expiry) or set an exact date.
     */
    public function renew(Page $page, ?Package $package = null, ?Carbon $until = null, bool $activate = true): void
    {
        DB::transaction(function () use ($page, $package, $until, $activate) {
            $base = ($page->expires_at && $page->expires_at->isFuture()) ? $page->expires_at : now();
            $newExpiry = $until ?? ($package ? $package->addDurationTo($base) : null);
            if (! $newExpiry) {
                throw new RuntimeException('Choose a package or a date.');
            }

            $page->expires_at = $newExpiry;
            $page->expiry_reminded_at = null;
            if ($activate && in_array($page->status, [PageStatus::Expired, PageStatus::Active], true) && $newExpiry->isFuture()) {
                $page->status = PageStatus::Active;
            }
            $page->save();

            if ($page->order && $page->status === PageStatus::Active && $page->order->status === OrderStatus::Expired) {
                $this->orders->transition($page->order, OrderStatus::Published, 'Renewed until '.$newExpiry->format('d M Y'));
            }

            ActivityLogger::log('page.renewed', $page, "/{$page->slug} until ".$newExpiry->format('d M Y'), ['package' => $package?->name]);
        });
    }

    /** Called by the scheduler. Public pages also check expiry live, so this is a safety net. */
    public function expireDue(): int
    {
        $count = 0;
        Page::where('status', PageStatus::Active)->whereNotNull('expires_at')->where('expires_at', '<', now())
            ->with('order')->chunkById(100, function ($pages) use (&$count) {
                foreach ($pages as $page) {
                    $page->update(['status' => PageStatus::Expired]);
                    if ($page->order && $page->order->status === OrderStatus::Published) {
                        $this->orders->transition($page->order, OrderStatus::Expired, 'Page expired automatically');
                    }
                    $count++;
                }
            });

        return $count;
    }

    public function duplicate(Page $page): Page
    {
        $copy = $page->replicate(['order_id', 'views_count', 'last_viewed_at', 'published_at', 'expiry_reminded_at']);
        $copy->slug = SlugRules::suggest($page->slug.'-copy');
        $copy->status = PageStatus::Draft;
        $copy->title = $page->title.' (copy)';
        $copy->save();

        foreach ($page->media as $m) {
            $copy->media()->create($m->only(['field_key', 'path', 'thumb_path', 'mime', 'original_name', 'size', 'width', 'height', 'sort_order', 'metadata']) + ['order_id' => null]);
        }
        ActivityLogger::log('page.duplicated', $copy, "/{$page->slug} → /{$copy->slug}");

        return $copy;
    }

    private function durationFromSnapshot(?Order $order, Carbon $from): Carbon
    {
        $v = (int) ($order?->snap('duration_value') ?: 1);

        return match ($order?->snap('duration_unit')) {
            'day' => $from->copy()->addDays($v),
            'year' => $from->copy()->addYearsNoOverflow($v),
            default => $from->copy()->addMonthsNoOverflow($v),
        };
    }

    private function guessName(Order $order): ?string
    {
        $c = $order->content ?? [];
        foreach (['their_name', 'birthday_name', 'to_name', 'business_name', 'brand_name', 'full_name', 'partner_one', 'title'] as $k) {
            if (! empty($c[$k]) && is_string($c[$k])) {
                $ascii = Str::slug(Str::ascii($c[$k]));
                if (strlen($ascii) >= 2) {
                    return $c[$k];
                }
            }
        }

        return null;
    }

    private function guessTitle(Order $order): string
    {
        $c = $order->content ?? [];
        $get = fn ($k) => isset($c[$k]) && is_string($c[$k]) ? trim($c[$k]) : '';

        $title = match (true) {
            $get('birthday_name') !== '' => 'শুভ জন্মদিন, '.$get('birthday_name').'!',
            $get('your_name') !== '' && $get('their_name') !== '' => $get('your_name').' ♥ '.$get('their_name'),
            $get('partner_one') !== '' && $get('partner_two') !== '' => $get('partner_one').' & '.$get('partner_two'),
            $get('to_name') !== '' => 'A letter for '.$get('to_name'),
            $get('business_name') !== '' => $get('business_name'),
            $get('brand_name') !== '' => $get('brand_name'),
            $get('full_name') !== '' => $get('full_name'),
            $get('title') !== '' => $get('title'),
            default => $order->customer_name,
        };

        return Str::limit($title, 120, '');
    }

    private function guessDescription(Order $order): string
    {
        $c = $order->content ?? [];
        foreach (['headline', 'tagline', 'summary', 'intro', 'subtitle', 'about', 'message', 'story', 'special_message'] as $k) {
            if (! empty($c[$k]) && is_string($c[$k])) {
                return trim(preg_replace('/\s+/', ' ', $c[$k]));
            }
        }

        return 'A GolpoBari page';
    }
}
