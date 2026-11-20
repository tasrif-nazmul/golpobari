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

        // The work queue: each list is one step of the daily flow, oldest first so nobody waits too long.
        $toVerify = Payment::with('order:id,order_number,customer_name,customer_phone,final_price,template_id,created_at', 'order.template:id,name')
            ->where('status', PaymentStatus::Pending)->oldest()->limit(10)->get();
        $dupes = Payment::whereIn('transaction_id', $toVerify->pluck('transaction_id'))
            ->select('transaction_id')->groupBy('transaction_id')->havingRaw('count(*) > 1')->pluck('transaction_id')->all();

        $toCreate = Order::with('template:id,name')
            ->whereIn('status', [OrderStatus::PaymentVerified, OrderStatus::InReview])->doesntHave('page')
            ->oldest()->limit(10)->get();

        $toFinish = Page::with(['order:id,order_number,customer_name,customer_phone', 'template:id,name'])
            ->whereIn('status', [PageStatus::Draft, PageStatus::Pending])->whereNotNull('order_id')
            ->orderByRaw('status = ? desc', [PageStatus::Pending->value])->oldest('updated_at')->limit(10)->get();

        $justLive = Page::with('order:id,order_number,customer_name,customer_phone')
            ->where('status', PageStatus::Active)->whereNotNull('order_id')
            ->where('published_at', '>=', now()->subDays(3))->latest('published_at')->limit(6)->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'revenue' => compact('verified', 'pending', 'refunded', 'discounts', 'month'),
            'actions' => AdminStats::pendingActions(),
            'toVerify' => $toVerify,
            'dupes' => $dupes,
            'toCreate' => $toCreate,
            'toFinish' => $toFinish,
            'justLive' => $justLive,
            'recent' => Order::with(['payment', 'template'])->latest('id')->limit(6)->get(),
            'expiring' => Page::with('order:id,customer_name,customer_phone')->expiringSoon((int) config('golpobari.expiring_soon_days', 7))->orderBy('expires_at')->limit(6)->get(),
            'today' => Order::where('created_at', '>=', now()->startOfDay())->count(),
            'finishCount' => Page::whereIn('status', [PageStatus::Draft, PageStatus::Pending])->whereNotNull('order_id')->count(),
        ]);
    }
}
