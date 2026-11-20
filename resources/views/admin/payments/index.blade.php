@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
@php $money = fn ($v) => '৳'.number_format((float) $v, fmod((float) $v, 1) ? 2 : 0); @endphp
<x-admin.header title="Payments" sub="Match each one with your bKash app before tapping Received." />

<div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
    <nav class="a-scroll" aria-label="Payment status">
        @foreach (['pending' => 'To check', 'verified' => 'Received', 'rejected' => 'Rejected', 'refunded' => 'Refunded', 'all' => 'All'] as $k => $v)
            <a href="{{ route('admin.payments.index', ['status' => $k]) }}" class="a-chip" aria-current="{{ $status === $k ? 'true' : 'false' }}">{{ $v }}@if ($k === 'pending' && ($adminBadges['payments'] ?? 0)) <span class="n {{ $status !== 'pending' ? 'hot' : '' }}">{{ $adminBadges['payments'] }}</span>@endif</a>
        @endforeach
    </nav>
    <form class="relative sm:ml-auto sm:w-72">
        <input type="hidden" name="status" value="{{ $status }}">
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
        <input name="q" value="{{ request('q') }}" class="a-input !pl-9" placeholder="TrxID, sender, order">
    </form>
</div>

<div class="space-y-3">
    @forelse ($payments as $p)
        @php $dup = in_array($p->transaction_id, $dupes, true); $isPending = $p->status->value === 'pending'; @endphp
        <div class="a-card p-4 sm:p-5 {{ $isPending ? '' : 'opacity-95' }}" x-data="{ reject: false }">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                <div class="flex min-w-0 flex-1 items-start gap-4">
                    <div class="w-24 shrink-0">
                        <p class="text-xl font-bold tabular-nums {{ $p->amountMismatch() ? 'text-rose-700' : '' }}">{{ $money($p->amount) }}</p>
                        @if ($p->amountMismatch())<p class="text-[11px] font-semibold leading-tight text-rose-700">should be {{ $money($p->expected_amount) }}</p>@endif
                    </div>
                    <div class="min-w-0 flex-1 text-sm">
                        <p class="flex flex-wrap items-center gap-2"><a href="{{ route('admin.orders.show', $p->order) }}" class="font-semibold hover:underline">{{ $p->order->customer_name }}</a>
                            <span class="font-mono text-xs text-stone-500">{{ $p->order->order_number }}</span>
                            @unless ($isPending)<x-admin.badge :color="$p->status->color()">{{ $p->status->value === 'verified' ? 'Received' : $p->status->label() }}</x-admin.badge>@endunless</p>
                        <p class="mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5 text-stone-600">
                            <span>from <span class="font-mono">{{ $p->sender_number }}</span></span>
                            <span x-data="copyText(@js($p->transaction_id), 'TrxID copied')">TrxID <button type="button" @click="copy()" class="font-mono font-semibold text-stone-900 underline decoration-stone-300 decoration-dashed underline-offset-4 hover:decoration-stone-900">{{ $p->transaction_id }}</button></span>
                            <span class="text-xs text-stone-500">{{ $p->created_at->format('d M, h:i A') }}</span>
                        </p>
                        @if ($dup)<p class="mt-1 text-xs font-semibold text-rose-700">This TrxID was used on another order too.</p>@endif
                        @if ($p->rejection_reason)<p class="mt-1 text-xs text-rose-800">Reason: {{ $p->rejection_reason }}</p>@endif
                    </div>
                </div>
                @if ($isPending)
                    <div class="grid grid-cols-[1fr_auto] gap-2 lg:flex">
                        <form method="POST" action="{{ route('admin.payments.verify', $p) }}" data-confirm="Did you receive {{ $money($p->amount) }} from {{ $p->sender_number }} (TrxID {{ $p->transaction_id }}) in bKash?">
                            @csrf<input type="hidden" name="make_page" value="1">
                            <button class="a-btn a-btn-success w-full">Received · make page</button>
                        </form>
                        <button type="button" class="a-btn a-btn-ghost" @click="reject = !reject">Reject</button>
                    </div>
                @else
                    <a href="{{ route('admin.orders.show', $p->order) }}" class="a-btn a-btn-ghost a-btn-sm self-start lg:self-center">Open order</a>
                @endif
            </div>
            @if ($isPending)
                <form x-show="reject" x-cloak method="POST" action="{{ route('admin.payments.reject', $p) }}" class="mt-3 flex gap-2">
                    @csrf<input name="reason" required maxlength="200" class="a-input" placeholder="Reason the customer will see, e.g. TrxID not found"><button class="a-btn a-btn-danger shrink-0">Reject</button>
                </form>
            @endif
        </div>
    @empty
        <div class="a-card px-5 py-12 text-center text-sm text-stone-500">{{ $status === 'pending' ? 'No payments waiting. All checked.' : 'Nothing here.' }}</div>
    @endforelse
</div>
<div class="mt-4">{{ $payments->links() }}</div>
@endsection
