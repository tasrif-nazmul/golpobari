@extends('layouts.admin')
@section('title', $coupon->code)
@section('content')
<x-admin.header :title="$coupon->code" :sub="$coupon->discountLabel().' · '.$coupon->statusLabel()" :back="route('admin.coupons.index')">
    <x-slot:actions><a href="{{ route('admin.coupons.edit', $coupon) }}" class="a-btn a-btn-ghost">Edit</a></x-slot:actions>
</x-admin.header>
<div class="mb-6 grid gap-3 sm:grid-cols-3">
    <div class="a-card p-4"><div class="text-xs uppercase tracking-wide text-stone-500">Times used</div><div class="text-2xl font-bold tabular-nums">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</div></div>
    <div class="a-card p-4"><div class="text-xs uppercase tracking-wide text-stone-500">Discount given</div><div class="text-2xl font-bold tabular-nums">৳{{ number_format((float) $coupon->usages()->sum('discount_amount')) }}</div></div>
    <div class="a-card p-4"><div class="text-xs uppercase tracking-wide text-stone-500">Valid</div><div class="text-sm font-semibold">{{ $coupon->starts_at?->format('d M Y') ?? 'Now' }} → {{ $coupon->expires_at?->format('d M Y') ?? 'No end' }}</div></div>
</div>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Discount</th><th>Order total</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        @forelse ($usages as $u)
            <tr>
                <td>@if ($u->order)<a href="{{ route('admin.orders.show', $u->order) }}" class="font-mono font-semibold hover:underline">{{ $u->order->order_number }}</a>@else—@endif</td>
                <td>{{ $u->order?->customer_name }}</td>
                <td class="font-mono">{{ $u->phone }}</td>
                <td class="tabular-nums">৳{{ number_format((float) $u->discount_amount, 2) }}</td>
                <td class="tabular-nums">৳{{ number_format((float) $u->order?->final_price, 2) }}</td>
                <td>@if ($u->order)@include('admin.partials.order-status', ['order' => $u->order])@endif</td>
                <td class="whitespace-nowrap text-stone-500">{{ $u->created_at->format('d M Y') }}</td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-12 text-center text-stone-500">Not used yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
{{ $usages->links() }}
@endsection
