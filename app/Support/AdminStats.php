<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;

class AdminStats
{
    /** Counts for the sidebar notification badges. */
    public static function badges(): array
    {
        return [
            'new' => Order::where('status', OrderStatus::PaymentPending)->count(),
            'payments' => Payment::where('status', PaymentStatus::Pending)->count(),
            'production' => Order::whereIn('status', [OrderStatus::PaymentVerified, OrderStatus::InReview, OrderStatus::InProduction, OrderStatus::Ready])->count(),
        ];
    }

    public static function pendingActions(): array
    {
        $days = (int) config('golpobari.expiring_soon_days', 7);

        return [
            'payments' => Payment::where('status', PaymentStatus::Pending)->count(),
            'to_create' => Order::whereIn('status', [OrderStatus::PaymentVerified, OrderStatus::InReview])->doesntHave('page')->count(),
            'in_production' => Order::where('status', OrderStatus::InProduction)->count(),
            'to_publish' => Page::where('status', PageStatus::Pending)->count(),
            'expiring' => Page::expiringSoon($days)->count(),
            'expired_unmarked' => Page::where('status', PageStatus::Active)->whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
        ];
    }
}
