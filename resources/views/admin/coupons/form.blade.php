@extends('layouts.admin')
@section('title', $coupon->exists ? 'Edit coupon' : 'New coupon')
@section('content')
<x-admin.header :title="$coupon->exists ? $coupon->code : 'New coupon'" :back="route('admin.coupons.index')">
    @if ($coupon->exists)
        <x-slot:actions>
            <a href="{{ route('admin.coupons.show', $coupon) }}" class="a-btn a-btn-ghost">Usage</a>
            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" data-confirm="Delete {{ $coupon->code }}? Past orders keep their discount.">@csrf @method('DELETE')<button class="a-btn a-btn-ghost text-rose-600">Delete</button></form>
        </x-slot:actions>
    @endif
</x-admin.header>
<form method="POST" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="max-w-3xl space-y-6" x-data="{ type: @js(old('type', $coupon->type)) }">
    @csrf @if ($coupon->exists) @method('PUT') @endif
    <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
        <x-admin.field name="code" label="Code" :value="$coupon->code" required class="font-mono uppercase" placeholder="EID25" />
        <x-admin.field name="description" label="Internal note" :value="$coupon->description" placeholder="Eid campaign on Facebook" />
        <div>
            <label class="a-label" for="f_type">Type</label>
            <select id="f_type" name="type" x-model="type" class="a-input"><option value="percent">Percentage off</option><option value="fixed">Fixed amount off</option></select>
        </div>
        <div>
            <label class="a-label" for="f_value">Value <span class="text-rose-500">*</span></label>
            <div class="flex items-center gap-2"><input id="f_value" type="number" step="0.01" min="0.01" name="value" value="{{ old('value', $coupon->value !== null ? (float) $coupon->value : '') }}" required class="a-input"><span class="text-sm text-stone-500" x-text="type === 'percent' ? '%' : '৳'"></span></div>
            @error('value')<p class="a-error">{{ $message }}</p>@enderror
        </div>
        <div x-show="type === 'percent'"><x-admin.field name="max_discount" label="Maximum discount (৳)" type="number" step="1" :value="$coupon->max_discount !== null ? (float) $coupon->max_discount : null" help="Optional cap." /></div>
        <x-admin.field name="min_order_amount" label="Minimum order (৳)" type="number" step="1" :value="$coupon->min_order_amount !== null ? (float) $coupon->min_order_amount : null" />
        <x-admin.field name="usage_limit" label="Total uses allowed" type="number" :value="$coupon->usage_limit" help="Empty = unlimited." />
        <x-admin.field name="per_phone_limit" label="Uses per phone number" type="number" :value="$coupon->per_phone_limit" help="Empty = unlimited." />
        <x-admin.field name="starts_at" label="Starts" type="datetime-local" :value="$coupon->starts_at?->format('Y-m-d\TH:i')" />
        <x-admin.field name="expires_at" label="Expires" type="datetime-local" :value="$coupon->expires_at?->format('Y-m-d\TH:i')" />
        <x-admin.field name="is_active" type="checkbox" label="Active" :value="$coupon->is_active" class="sm:col-span-2" />
    </section>
    <p class="text-sm text-stone-500">Coupons are checked on the server when an order is placed. A use is given back if you cancel the order. A rejected payment keeps the use, since the customer can send a corrected payment for the same order.</p>
    @include('admin.partials.form-footer', ['cancel' => route('admin.coupons.index')])
</form>
@endsection
