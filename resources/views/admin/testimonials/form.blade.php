@extends('layouts.admin')
@section('title', $item->exists ? 'Edit testimonial' : 'New testimonial')
@section('content')
<x-admin.header :title="$item->exists ? $item->name : 'New testimonial'" :back="route('admin.testimonials.index')" />
<form method="POST" action="{{ $item->exists ? route('admin.testimonials.update', $item) : route('admin.testimonials.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf @if ($item->exists) @method('PUT') @endif
    <section class="a-card grid gap-4 p-5 sm:grid-cols-2">
        <x-admin.field name="name" label="Name" :value="$item->name" required />
        <x-admin.field name="role" label="Detail" :value="$item->role" placeholder="Anniversary page, Dhaka" />
        <x-admin.field name="quote" label="What they said" type="textarea" rows="4" :value="$item->quote" required class="sm:col-span-2" />
        <x-admin.field name="rating" label="Rating" type="select" :value="$item->rating" :options="[5 => '★★★★★', 4 => '★★★★', 3 => '★★★', 2 => '★★', 1 => '★']" />
        <x-admin.field name="sort_order" label="Sort order" type="number" :value="$item->sort_order" />
        <div class="sm:col-span-2">
            <x-admin.field name="avatar" label="Photo (optional)" type="file" />
            @if ($item->avatarUrl())<img src="{{ $item->avatarUrl() }}" alt="" class="mt-2 h-12 w-12 rounded-full object-cover">@endif
        </div>
        <x-admin.field name="is_active" type="checkbox" label="Show on the website" :value="$item->is_active" class="sm:col-span-2" />
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.testimonials.index')])
</form>
@endsection
