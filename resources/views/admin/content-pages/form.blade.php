@extends('layouts.admin')
@section('title', $item->title)
@section('content')
<x-admin.header :title="$item->title" :sub="'/'.$item->slug" :back="route('admin.content-pages.index')">
    <x-slot:actions><a href="{{ url('/'.$item->slug) }}" target="_blank" class="a-btn a-btn-ghost">View ↗</a></x-slot:actions>
</x-admin.header>
<form method="POST" action="{{ route('admin.content-pages.update', $item) }}" class="max-w-4xl space-y-6">
    @csrf @method('PUT')
    <section class="a-card space-y-4 p-5">
        <x-admin.field name="title" label="Title" :value="$item->title" required />
        <x-admin.field name="meta_description" label="Search description" :value="$item->meta_description" />
        <x-admin.field name="body" label="Content" type="textarea" rows="22" :value="$item->body" required class="font-mono" help="Markdown: ## Heading, **bold**, - list item, [link](https://…). Raw HTML is stripped." />
    </section>
    @include('admin.partials.form-footer', ['cancel' => route('admin.content-pages.index')])
</form>
@endsection
