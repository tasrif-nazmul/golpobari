@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php $money = fn ($v) => '৳'.number_format((float) $v, fmod((float) $v, 1) ? 2 : 0); @endphp
<x-admin.header title="Dashboard" :sub="now()->format('l, d F Y')">
    <x-slot:actions><a href="{{ route('admin.payments.index') }}" class="a-btn a-btn-brand">Verify payments</a></x-slot:actions>
</x-admin.header>

{{-- Pending actions --}}
@php
    $todo = array_filter([
        ['payments', 'payment(s) waiting for verification', route('admin.payments.index'), 'amber'],
        ['to_create', 'verified order(s) waiting for a page', route('admin.orders.index', ['status' => 'payment_verified']), 'sky'],
        ['in_production', 'page(s) in production', route('admin.orders.index', ['status' => 'in_production']), 'violet'],
        ['to_publish', 'page(s) ready to publish', route('admin.pages.index', ['tab' => 'pending']), 'teal'],
        ['expiring', 'page(s) expiring in '.config('golpobari.expiring_soon_days').' days', route('admin.pages.index', ['tab' => 'expiring']), 'orange'],
        ['expired_unmarked', 'page(s) past expiry. Check that the cron job is running', route('admin.pages.index', ['tab' => 'active', 'sort' => 'expires_at', 'dir' => 'asc']), 'rose'],
    ], fn ($t) => ($actions[$t[0]] ?? 0) > 0);
@endphp
@if ($todo)
    <div class="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        @foreach ($todo as [$k, $label, $url, $color])
            <a href="{{ $url }}" class="a-card flex items-center gap-3 p-4 transition hover:ring-stone-400">
                <x-admin.badge :color="$color" class="!text-base !px-3">{{ $actions[$k] }}</x-admin.badge>
                <span class="text-sm font-medium">{{ $label }}</span>
                <span class="ml-auto text-stone-400">→</span>
            </a>
        @endforeach
    </div>
@else
    <div class="a-card mb-6 p-4 text-sm text-stone-600">✓ Nothing waiting. All caught up.</div>
@endif

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['Total orders', $stats['total_orders'], route('admin.orders.index')],
        ['Payment pending', $stats['payment_pending'], route('admin.orders.index', ['status' => 'payment_pending'])],
        ['Payment verified', $stats['payment_verified'], route('admin.orders.index', ['status' => 'payment_verified'])],
        ['In production', $stats['in_production'], route('admin.orders.index', ['status' => 'in_production'])],
        ['Delivered / published', $stats['delivered'], route('admin.orders.index', ['status' => 'published'])],
        ['Active pages', $stats['active_pages'], route('admin.pages.index', ['tab' => 'active'])],
        ['Expired pages', $stats['expired_pages'], route('admin.pages.index', ['tab' => 'expired'])],
        ['Coupon uses', $stats['coupon_uses'], route('admin.coupons.index', ['filter' => 'used'])],
    ] as [$label, $value, $url])
        <a href="{{ $url }}" class="a-card p-4 transition hover:ring-stone-400">
            <p class="text-xs font-medium uppercase tracking-wide text-stone-500">{{ $label }}</p>
            <p class="mt-1 text-2xl font-bold tabular-nums">{{ number_format($value) }}</p>
        </a>
    @endforeach
</div>

<div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
    <div class="a-card p-4 sm:col-span-2 lg:col-span-1 bg-stone-950 !ring-stone-950 text-white">
        <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Total revenue</p>
        <p class="mt-1 text-2xl font-bold tabular-nums">{{ $money($revenue['verified']) }}</p>
        <p class="text-xs text-stone-400">Verified payments only</p>
    </div>
    <div class="a-card p-4"><p class="text-xs font-medium uppercase tracking-wide text-stone-500">This month</p><p class="mt-1 text-xl font-bold tabular-nums">{{ $money($revenue['month']) }}</p></div>
    <div class="a-card p-4"><p class="text-xs font-medium uppercase tracking-wide text-stone-500">Pending (not counted)</p><p class="mt-1 text-xl font-bold tabular-nums text-amber-700">{{ $money($revenue['pending']) }}</p></div>
    <div class="a-card p-4"><p class="text-xs font-medium uppercase tracking-wide text-stone-500">Refunded</p><p class="mt-1 text-xl font-bold tabular-nums text-rose-700">{{ $money($revenue['refunded']) }}</p></div>
    <div class="a-card p-4"><p class="text-xs font-medium uppercase tracking-wide text-stone-500">Discounts given</p><p class="mt-1 text-xl font-bold tabular-nums">{{ $money($revenue['discounts']) }}</p></div>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[2fr_1fr]">
    <section class="a-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-stone-200 px-4 py-3"><h2 class="font-semibold">Recent orders</h2><a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-stone-500 hover:text-stone-900">All orders →</a></div>
        <div class="overflow-x-auto">
            <table class="a-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($recent as $o)
                    <tr>
                        <td><a class="font-mono font-semibold hover:underline" href="{{ route('admin.orders.show', $o) }}">{{ $o->order_number }}</a><div class="text-xs text-stone-500">{{ $o->created_at->diffForHumans() }}</div></td>
                        <td>{{ $o->customer_name }}<div class="text-xs text-stone-500">{{ $o->templateName() }}</div></td>
                        <td class="tabular-nums">{{ $money($o->final_price) }}</td>
                        <td>@include('admin.partials.payment-status', ['status' => $o->payment_status])</td>
                        <td>@include('admin.partials.order-status', ['order' => $o])</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-10 text-center text-stone-500">No orders yet. Share your site link to get the first one!</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
    <section class="a-card">
        <div class="border-b border-stone-200 px-4 py-3"><h2 class="font-semibold">Expiring soon</h2></div>
        <ul class="divide-y divide-stone-100">
            @forelse ($expiring as $p)
                <li class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                    <a href="{{ route('admin.pages.edit', $p) }}" class="font-medium hover:underline">/{{ $p->slug }}</a>
                    <span class="text-stone-500">{{ $p->expires_at->diffForHumans() }}</span>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-stone-500">No pages expiring this week.</li>
            @endforelse
        </ul>
    </section>
</div>
@endsection
