@extends('layouts.admin')
@section('title', $package->exists ? 'Edit package' : 'New package')
@section('content')
<x-admin.header :title="$package->exists ? $package->name : 'New package'" :back="route('admin.packages.index')" />
<form method="POST" action="{{ $package->exists ? route('admin.packages.update', $package) : route('admin.packages.store') }}" class="max-w-3xl space-y-6">
    @csrf @if ($package->exists) @method('PUT') @endif
    <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
        <x-admin.field name="name" label="Name (English)" :value="$package->name" required placeholder="3 Months" />
        <x-admin.field name="name_bn" label="Name (Bangla)" :value="$package->name_bn" placeholder="৩ মাস" />
        <x-admin.field name="slug" label="Key" :value="$package->slug" help="Made from the name if empty." />
        <div class="grid grid-cols-2 gap-2">
            <x-admin.field name="duration_value" label="Duration" type="number" :value="$package->duration_value" required min="1" />
            <x-admin.field name="duration_unit" label="&nbsp;" type="select" :value="$package->duration_unit" :options="['day' => 'Days', 'month' => 'Months', 'year' => 'Years']" />
        </div>
        <x-admin.field name="price" label="Price (৳)" type="number" step="1" :value="$package->price !== null ? (float) $package->price : null" required min="0" />
        <x-admin.field name="compare_price" label="Crossed-out price (৳)" type="number" step="1" :value="$package->compare_price !== null ? (float) $package->compare_price : null" help="Optional. Shown struck through next to the price." />
        <x-admin.field name="description" label="Short description" :value="$package->description" class="sm:col-span-2" />
        <x-admin.field name="features" label="Features" type="textarea" rows="5" :value="implode(PHP_EOL, $package->features ?? [])" help="One per line. Shown as a checklist on the pricing page." class="sm:col-span-2" />
        <x-admin.field name="max_photos" label="Photo limit" type="number" :value="$package->flag('max_photos')" help="Empty means the template's own limit." />
        <x-admin.field name="sort_order" label="Sort order" type="number" :value="$package->sort_order" />
        <div class="space-y-2 sm:col-span-2">
            <x-admin.field name="show_branding" type="checkbox" label="Show “Made with GolpoBari” on pages" :value="$package->flag('show_branding', true)" />
            <x-admin.field name="premium_templates" type="checkbox" label="Allows premium templates" :value="$package->flag('premium_templates', false)" />
            <x-admin.field name="is_featured" type="checkbox" label="Mark as most popular" :value="$package->is_featured" />
            <x-admin.field name="is_active" type="checkbox" label="Available to order" :value="$package->is_active" />
        </div>
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.packages.index')])
</form>
@endsection
