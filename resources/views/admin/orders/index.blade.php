@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
@php use App\Enums\OrderStatus; use App\Enums\PaymentStatus; @endphp
<x-admin.header title="Orders" :sub="$orders->total().' orders'" />

<form method="GET" class="a-card mb-4 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-7">
    <input name="q" value="{{ request('q') }}" class="a-input lg:col-span-2" placeholder="Order ID, name, phone, email, TrxID, slug">
    <select name="status" class="a-input"><option value="">All statuses</option>@foreach (OrderStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
    <select name="payment" class="a-input"><option value="">All payments</option>@foreach (PaymentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('payment') === $k)>{{ $v }}</option>@endforeach</select>
    <select name="category" class="a-input"><option value="">All categories</option>@foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <select name="package" class="a-input"><option value="">All packages</option>@foreach ($packages as $id => $n)<option value="{{ $id }}" @selected((string) request('package') === (string) $id)>{{ $n }}</option>@endforeach</select>
    <div class="flex gap-2"><button class="a-btn a-btn-primary flex-1">Filter</button>@if (request()->except('page'))<a href="{{ route('admin.orders.index') }}" class="a-btn a-btn-ghost">Reset</a>@endif</div>
    <label class="flex items-center gap-2 text-sm text-stone-500">From <input type="date" name="from" value="{{ request('from') }}" class="a-input"></label>
    <label class="flex items-center gap-2 text-sm text-stone-500">To <input type="date" name="to" value="{{ request('to') }}" class="a-input"></label>
</form>

<div class="a-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="a-table">
            <thead><tr>
                <th><x-admin.sort-link column="order_number" label="Order" /></th>
                <th><x-admin.sort-link column="customer_name" label="Customer" /></th>
                <th>Template / package</th>
                <th><x-admin.sort-link column="final_price" label="Amount" /></th>
                <th>Payment</th>
                <th><x-admin.sort-link column="status" label="Status" /></th>
                <th>Page</th>
                <th><x-admin.sort-link column="created_at" label="Date" /></th>
            </tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $o) }}" class="font-mono font-semibold hover:underline">{{ $o->order_number }}</a></td>
                    <td>{{ $o->customer_name }}<div class="text-xs text-stone-500">{{ $o->customer_phone }}</div></td>
                    <td>{{ $o->templateName() }}<div class="text-xs text-stone-500">{{ $o->packageName() }}@if ($o->coupon_code) · <span class="font-mono">{{ $o->coupon_code }}</span>@endif</div></td>
                    <td class="tabular-nums">৳{{ number_format((float) $o->final_price, 2) }}</td>
                    <td>@include('admin.partials.payment-status', ['status' => $o->payment_status])<div class="font-mono text-xs text-stone-500">{{ $o->payment?->transaction_id }}</div></td>
                    <td>@include('admin.partials.order-status', ['order' => $o])</td>
                    <td>@if ($o->page)<a href="{{ route('admin.pages.edit', $o->page) }}" class="text-sm hover:underline">/{{ $o->page->slug }}</a>@else<span class="text-stone-400">—</span>@endif</td>
                    <td class="whitespace-nowrap text-stone-500">{{ $o->created_at->format('d M Y') }}<div class="text-xs">{{ $o->created_at->format('h:i A') }}</div></td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-12 text-center text-stone-500">No orders match.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $orders->links() }}
@endsection
