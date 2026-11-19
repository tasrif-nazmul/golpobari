@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
<x-admin.header title="Coupons" :sub="$coupons->total().' coupons'">
    <x-slot:actions><a href="{{ route('admin.coupons.create') }}" class="a-btn a-btn-primary">+ New coupon</a></x-slot:actions>
</x-admin.header>
<div class="mb-4 flex flex-wrap items-center gap-2">
    @foreach (['' => 'All', 'active' => 'Active', 'used' => 'Used', 'expired' => 'Expired', 'disabled' => 'Disabled'] as $k => $v)
        <a href="{{ route('admin.coupons.index', array_filter(['filter' => $k])) }}" class="a-btn a-btn-sm {{ (string) $filter === $k ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $v }}</a>
    @endforeach
    <form class="ml-auto"><input type="hidden" name="filter" value="{{ $filter }}"><input name="q" value="{{ request('q') }}" class="a-input !min-h-9 uppercase" placeholder="Search code"></form>
</div>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th>Code</th><th>Discount</th><th><a href="{{ request()->fullUrlWithQuery(['sort' => 'usage']) }}" class="hover:text-stone-900">Used</a></th><th>Given away</th><th>Valid</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($coupons as $c)
            @php $st = $c->statusLabel(); @endphp
            <tr>
                <td><a href="{{ route('admin.coupons.show', $c) }}" class="font-mono font-semibold hover:underline">{{ $c->code }}</a><div class="text-xs text-stone-500">{{ $c->description }}</div></td>
                <td>{{ $c->discountLabel() }}@if ($c->max_discount)<div class="text-xs text-stone-500">max ৳{{ number_format((float) $c->max_discount) }}</div>@endif @if ($c->min_order_amount)<div class="text-xs text-stone-500">min order ৳{{ number_format((float) $c->min_order_amount) }}</div>@endif</td>
                <td class="tabular-nums">{{ $c->used_count }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}@if ($c->per_phone_limit)<div class="text-xs text-stone-500">{{ $c->per_phone_limit }} per phone</div>@endif</td>
                <td class="tabular-nums">৳{{ number_format((float) $c->usages_sum_discount_amount) }}</td>
                <td class="whitespace-nowrap text-xs text-stone-500">{{ $c->starts_at?->format('d M Y') ?? 'Now' }} → {{ $c->expires_at?->format('d M Y') ?? 'No end' }}</td>
                <td><x-admin.badge :color="['Active' => 'emerald', 'Scheduled' => 'sky', 'Expired' => 'orange', 'Used up' => 'amber', 'Disabled' => 'stone'][$st] ?? 'stone'">{{ $st }}</x-admin.badge></td>
                <td class="whitespace-nowrap text-right"><a href="{{ route('admin.coupons.edit', $c) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="py-12 text-center text-stone-500">No coupons.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
{{ $coupons->links() }}
@endsection
