@extends('layouts.admin')
@section('title', 'Payments')
@section('content')
<x-admin.header title="Payments" sub="Check each one against your bKash statement before verifying." />
<div class="mb-4 flex flex-wrap items-center gap-2">
    @foreach (['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected', 'refunded' => 'Refunded', 'all' => 'All'] as $k => $v)
        <a href="{{ route('admin.payments.index', ['status' => $k]) }}" class="a-btn a-btn-sm {{ $status === $k ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $v }}</a>
    @endforeach
    <form class="ml-auto"><input type="hidden" name="status" value="{{ $status }}"><input name="q" value="{{ request('q') }}" class="a-input !min-h-9" placeholder="TrxID, sender, order"></form>
</div>
<div class="a-card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="a-table">
            <thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Sender</th><th>Transaction ID</th><th>Submitted</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @forelse ($payments as $p)
                <tr x-data="{ reject: false }">
                    <td><a href="{{ route('admin.orders.show', $p->order) }}" class="font-mono font-semibold hover:underline">{{ $p->order->order_number }}</a></td>
                    <td>{{ $p->order->customer_name }}<div class="text-xs text-stone-500">{{ $p->order->customer_phone }}</div></td>
                    <td class="tabular-nums"><span class="font-semibold {{ $p->amountMismatch() ? 'text-rose-600' : '' }}">৳{{ number_format((float) $p->amount, 2) }}</span>@if ($p->amountMismatch())<div class="text-xs text-rose-600">expected ৳{{ number_format((float) $p->expected_amount, 2) }}</div>@endif</td>
                    <td class="font-mono">{{ $p->sender_number }}</td>
                    <td class="font-mono font-semibold">{{ $p->transaction_id }}@if (in_array($p->transaction_id, $dupes, true))<div class="text-xs font-semibold text-rose-600">⚠ reused</div>@endif</td>
                    <td class="whitespace-nowrap text-stone-500">{{ $p->created_at->format('d M, h:i A') }}</td>
                    <td><x-admin.badge :color="$p->status->color()">{{ $p->status->label() }}</x-admin.badge></td>
                    <td class="text-right">
                        @if ($p->status->value === 'pending')
                            <div class="flex justify-end gap-1.5">
                                <form method="POST" action="{{ route('admin.payments.verify', $p) }}" data-confirm="Verify ৳{{ number_format((float) $p->amount, 2) }} from {{ $p->sender_number }} (TrxID {{ $p->transaction_id }})?">@csrf<button class="a-btn a-btn-success a-btn-sm">Verify</button></form>
                                <button type="button" class="a-btn a-btn-ghost a-btn-sm" @click="reject = !reject">Reject</button>
                            </div>
                            <form x-show="reject" x-cloak method="POST" action="{{ route('admin.payments.reject', $p) }}" class="mt-2 flex gap-1.5">
                                @csrf<input name="reason" required maxlength="200" class="a-input !min-h-8 text-xs" placeholder="Reason"><button class="a-btn a-btn-danger a-btn-sm">OK</button>
                            </form>
                        @else
                            <a href="{{ route('admin.orders.show', $p->order) }}" class="a-btn a-btn-ghost a-btn-sm">Open</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-12 text-center text-stone-500">{{ $status === 'pending' ? 'No payments waiting. 🎉' : 'Nothing here.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
{{ $payments->links() }}
@endsection
