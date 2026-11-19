@extends('layouts.admin')
@section('title', $item->exists ? 'Edit question' : 'New question')
@section('content')
<x-admin.header :title="$item->exists ? 'Edit question' : 'New question'" :back="route('admin.faqs.index')" />
<form method="POST" action="{{ $item->exists ? route('admin.faqs.update', $item) : route('admin.faqs.store') }}" class="max-w-2xl space-y-6">
    @csrf @if ($item->exists) @method('PUT') @endif
    <section class="a-card space-y-4 p-5">
        <x-admin.field name="question" label="Question" :value="$item->question" required />
        <x-admin.field name="answer" label="Answer" type="textarea" rows="6" :value="$item->answer" required help="Plain text. Line breaks are kept." />
        <x-admin.field name="sort_order" label="Sort order" type="number" :value="$item->sort_order" />
        <x-admin.field name="is_active" type="checkbox" label="Show on the website" :value="$item->is_active" />
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.faqs.index')])
</form>
@endsection
