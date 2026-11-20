@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
@php
    use App\Enums\OrderStatus; use App\Enums\PaymentStatus;
    $more = collect(['status', 'payment', 'category', 'package', 'from', 'to'])->contains(fn ($k) => filled(request($k)));
@endphp
<x-admin.header title="Orders" :sub="$orders->total().' '.Str::plural('order', $orders->total()).($stage ? ' · '.$stages[$stage][0] : '')" />

{{-- Quick tabs by step --}}
<nav class="a-scroll mb-4" aria-label="Order steps">
    <a href="{{ route('admin.orders.index', request()->only('q')) }}" class="a-chip" aria-current="{{ $stage ? 'false' : 'true' }}">All <span class="n">{{ $total }}</span></a>
    @foreach ($stages as $key => [$label])
        @php $n = $stageCounts[$key]; $hot = $n && in_array($key, ['check', 'make'], true); @endphp
        <a href="{{ route('admin.orders.index', ['stage' => $key] + request()->only('q')) }}" class="a-chip" aria-current="{{ $stage === $key ? 'true' : 'false' }}">{{ $label }} <span class="n {{ $hot && $stage !== $key ? 'hot' : '' }}">{{ $n }}</span></a>
    @endforeach
</nav>

<form method="GET" class="mb-4" x-data="{ more: {{ $more ? 'true' : 'false' }} }">
    @if ($stage)<input type="hidden" name="stage" value="{{ $stage }}">@endif
    <div class="flex gap-2">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
            <input name="q" value="{{ request('q') }}" class="a-input !pl-9" placeholder="Order ID, name, phone, email, TrxID, link">
        </div>
        <button class="a-btn a-btn-primary">Search</button>
        <button type="button" class="a-btn a-btn-ghost" @click="more = !more" :aria-expanded="more">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M7 12h10M10 18h4"/></svg><span class="hidden sm:inline">Filters</span>
        </button>
    </div>
    <div x-show="more" x-cloak class="a-card mt-2 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-6">
        <select name="status" class="a-input"><option value="">Any status</option>@foreach (OrderStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>@endforeach</select>
        <select name="payment" class="a-input"><option value="">Any payment</option>@foreach (PaymentStatus::options() as $k => $v)<option value="{{ $k }}" @selected(request('payment') === $k)>{{ $v }}</option>@endforeach</select>
        <select name="category" class="a-input"><option value="">All categories</option>@foreach ($categories as $id => $n)<option value="{{ $id }}" @selected((string) request('category') === (string) $id)>{{ $n }}</option>@endforeach</select>
        <select name="package" class="a-input"><option value="">All packages</option>@foreach ($packages as $id => $n)<option value="{{ $id }}" @selected((string) request('package') === (string) $id)>{{ $n }}</option>@endforeach</select>
        <label class="flex items-center gap-2 text-sm text-stone-500">From <input type="date" name="from" value="{{ request('from') }}" class="a-input"></label>
        <label class="flex items-center gap-2 text-sm text-stone-500">To <input type="date" name="to" value="{{ request('to') }}" class="a-input"></label>
        <div class="flex gap-2 sm:col-span-2 lg:col-span-6 lg:justify-end">
            @if (request()->except(['page', 'stage']))<a href="{{ route('admin.orders.index', $stage ? ['stage' => $stage] : []) }}" class="a-btn a-btn-ghost">Clear</a>@endif
            <button class="a-btn a-btn-primary">Apply</button>
        </div>
    </div>
</form>

{{-- Phone: cards --}}
<div class="space-y-2 md:hidden">
    @forelse ($orders as $o)
        <div class="a-card p-4">
            <a href="{{ route('admin.orders.show', $o) }}" class="block">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $o->customer_name }}</p>
                        <p class="font-mono text-xs text-stone-500">{{ $o->order_number }} · {{ $o->created_at->format('d M, h:i A') }}</p>
                    </div>
                    <p class="shrink-0 font-semibold tabular-nums">৳{{ number_format((float) $o->final_price) }}</p>
                </div>
                <p class="mt-1 truncate text-sm text-stone-600">{{ $o->templateName() }} · {{ $o->packageName() }}</p>
            </a>
            <div class="mt-3 flex items-center justify-between gap-2 border-t border-dashed border-stone-200 pt-3">
                @include('admin.partials.order-status', ['order' => $o])
                @include('admin.partials.order-next', ['order' => $o])
            </div>
        </div>
    @empty
        <div class="a-card px-5 py-12 text-center text-sm text-stone-500">No orders here.</div>
    @endforelse
</div>

{{-- Tablet/desktop: table --}}
<div class="a-card hidden overflow-hidden md:block">
    <div class="overflow-x-auto">
        <table class="a-table">
            <thead><tr>
                <th><x-admin.sort-link column="customer_name" label="Customer" /></th>
                <th>Template</th>
                <th class="text-right"><x-admin.sort-link column="final_price" label="Amount" /></th>
                <th><x-admin.sort-link column="status" label="Status" /></th>
                <th><x-admin.sort-link column="created_at" label="Date" /></th>
                <th class="text-right">Next step</th>
            </tr></thead>
            <tbody>
            @forelse ($orders as $o)
                <tr>
                    <td>
                        <a href="{{ route('admin.orders.show', $o) }}" class="font-semibold hover:underline">{{ $o->customer_name }}</a>
                        <div class="text-xs text-stone-500"><span class="font-mono">{{ $o->order_number }}</span> · {{ $o->customer_phone }}</div>
                    </td>
                    <td>{{ $o->templateName() }}<div class="text-xs text-stone-500">{{ $o->packageName() }}@if ($o->coupon_code) · <span class="font-mono">{{ $o->coupon_code }}</span>@endif</div></td>
                    <td class="text-right tabular-nums">৳{{ number_format((float) $o->final_price, fmod((float) $o->final_price, 1) ? 2 : 0) }}
                        @if ($o->payment?->transaction_id)<div class="font-mono text-xs text-stone-500">{{ $o->payment->transaction_id }}</div>@endif</td>
                    <td>@include('admin.partials.order-status', ['order' => $o])
                        @if ($o->page)<div class="mt-1"><a href="{{ route('admin.pages.edit', $o->page) }}" class="font-mono text-xs text-stone-500 hover:underline">/{{ $o->page->slug }}</a></div>@endif</td>
                    <td class="whitespace-nowrap text-stone-500">{{ $o->created_at->format('d M Y') }}<div class="text-xs">{{ $o->created_at->format('h:i A') }}</div></td>
                    <td class="text-right"><div class="flex justify-end">@include('admin.partials.order-next', ['order' => $o])</div></td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-12 text-center text-stone-500">No orders here.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $orders->links() }}</div>
@endsection
