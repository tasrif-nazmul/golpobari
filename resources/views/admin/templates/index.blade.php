@extends('layouts.admin')
@section('title', 'Templates')
@section('content')
<x-admin.header title="Templates" :sub="$templates->count().' templates'">
    <x-slot:actions><a href="{{ route('admin.templates.create') }}" class="a-btn a-btn-primary">+ New template</a></x-slot:actions>
</x-admin.header>
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.templates.index') }}" class="a-btn a-btn-sm {{ ! request('category') ? 'a-btn-primary' : 'a-btn-ghost' }}">All</a>
    @foreach ($categories as $id => $n)<a href="{{ route('admin.templates.index', ['category' => $id]) }}" class="a-btn a-btn-sm {{ (string) request('category') === (string) $id ? 'a-btn-primary' : 'a-btn-ghost' }}">{{ $n }}</a>@endforeach
</div>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse ($templates as $t)
        <article class="a-card flex flex-col overflow-hidden">
            <div class="relative aspect-[16/10] bg-stone-100">
                @if ($t->previewUrl())<img src="{{ $t->previewUrl() }}" alt="" loading="lazy" class="h-full w-full object-cover object-top">@else<div class="grid h-full place-items-center text-sm text-stone-400">No preview image</div>@endif
                <div class="absolute left-2 top-2 flex gap-1">
                    @unless ($t->is_active)<x-admin.badge>Hidden</x-admin.badge>@endunless
                    @if ($t->is_featured)<x-admin.badge color="amber">Featured</x-admin.badge>@endif
                    @if ($t->is_premium)<x-admin.badge color="violet">Premium</x-admin.badge>@endif
                </div>
            </div>
            <div class="flex flex-1 flex-col p-4">
                <div class="flex items-start justify-between gap-2">
                    <div><h2 class="font-semibold">{{ $t->name }}</h2><p class="text-xs text-stone-500">{{ $t->category?->name }} · {{ $t->fields_count }} fields · up to {{ $t->max_photos }} photos</p></div>
                    @if (request('category'))@include('admin.partials.reorder', ['type' => 'templates', 'id' => $t->id, 'first' => $loop->first, 'last' => $loop->last])@endif
                </div>
                <p class="mt-1 text-xs text-stone-500">{{ number_format($orderCounts[$t->id] ?? 0) }} orders</p>
                <div class="mt-auto flex flex-wrap gap-1.5 pt-3">
                    <a href="{{ route('admin.templates.edit', $t) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                    <a href="{{ route('templates.render', $t) }}" target="_blank" class="a-btn a-btn-ghost a-btn-sm">Sample ↗</a>
                    <a href="{{ route('admin.templates.create', ['layout' => $t->layout]) }}" class="a-btn a-btn-ghost a-btn-sm">New from layout</a>
                </div>
            </div>
        </article>
    @empty
        <p class="text-stone-500">No templates in this category.</p>
    @endforelse
</div>
@endsection
