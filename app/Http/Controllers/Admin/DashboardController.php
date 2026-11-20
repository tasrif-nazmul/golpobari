<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PageStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use App\Support\AdminStats;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $byStatus = Order::select('status', DB::raw('count(*) as c'))->groupBy('status')->pluck('c', 'status');
        $s = fn (OrderStatus ...$st) => collect($st)->sum(fn ($x) => (int) ($byStatus[$x->value] ?? 0));

        // Revenue = verified payments only. Refunded and rejected money is never counted.
        $verified = (float) Payment::where('status', PaymentStatus::Verified)->sum('amount');
        $pending = (float) Payment::where('status', PaymentStatus::Pending)->sum('amount');
        $refunded = (float) Payment::where('status', PaymentStatus::Refunded)->sum('amount');
        $discounts = (float) Order::whereIn('payment_status', [PaymentStatus::Verified])->sum(DB::raw('discount_amount + manual_discount'));
        $month = (float) Payment::where('status', PaymentStatus::Verified)->where('verified_at', '>=', now()->startOfMonth())->sum('amount');

        $stats = [
            'total_orders' => (int) $byStatus->sum(),
            'payment_pending' => $s(OrderStatus::PaymentPending, OrderStatus::Submitted),
            'payment_verified' => $s(OrderStatus::PaymentVerified, OrderStatus::InReview),
            'in_production' => $s(OrderStatus::InProduction, OrderStatus::Ready),
            'delivered' => $s(OrderStatus::Delivered, OrderStatus::Published),
            'active_pages' => Page::where('status', PageStatus::Active)->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count(),
            'expired_pages' => Page::where(fn ($q) => $q->where('status', PageStatus::Expired)->orWhere(fn ($w) => $w->where('status', PageStatus::Active)->where('expires_at', '<', now())))->count(),
            'coupon_uses' => CouponUsage::count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'revenue' => compact('verified', 'pending', 'refunded', 'discounts', 'month'),
            'actions' => AdminStats::pendingActions(),
            'recent' => Order::with(['payment', 'template'])->latest('id')->limit(8)->get(),
            'expiring' => Page::expiringSoon((int) config('golpobari.expiring_soon_days', 7))->orderBy('expires_at')->limit(5)->get(),
        ]);
    }
}
