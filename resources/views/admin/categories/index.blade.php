@extends('layouts.admin')
@section('title', 'Categories')
@section('content')
<x-admin.header title="Categories" sub="The occasions customers choose from. Order here is the order on the site.">
    <x-slot:actions><a href="{{ route('admin.categories.create') }}" class="a-btn a-btn-primary">+ New category</a></x-slot:actions>
</x-admin.header>
<div class="a-card overflow-hidden"><div class="overflow-x-auto">
    <table class="a-table">
        <thead><tr><th class="w-10"></th><th>Category</th><th>Address</th><th>Templates</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse ($categories as $c)
            <tr>
                <td>@include('admin.partials.reorder', ['type' => 'categories', 'id' => $c->id, 'first' => $loop->first, 'last' => $loop->last])</td>
                <td><div class="flex items-center gap-3">
                    @if ($c->iconUrl())<img src="{{ $c->iconUrl() }}" alt="" class="h-9 w-9 rounded-lg object-cover">@else<span class="grid h-9 w-9 place-items-center rounded-lg text-lg" style="background: {{ $c->accent_color ?: '#eee' }}22">{{ $c->emoji ?: '•' }}</span>@endif
                    <div><div class="font-semibold">{{ $c->name }}</div><div class="text-xs text-stone-500">{{ $c->name_bn }}</div></div>
                </div></td>
                <td class="font-mono text-xs text-stone-500">/categories/{{ $c->slug }}</td>
                <td class="tabular-nums"><a href="{{ route('admin.templates.index', ['category' => $c->id]) }}" class="hover:underline">{{ $c->templates_count }}</a></td>
                <td>@if ($c->is_active)<x-admin.badge color="emerald">Active</x-admin.badge>@else<x-admin.badge>Hidden</x-admin.badge>@endif</td>
                <td class="whitespace-nowrap text-right">
                    <a href="{{ route('categories.show', $c) }}" target="_blank" class="a-btn a-btn-ghost a-btn-sm">View ↗</a>
                    <a href="{{ route('admin.categories.edit', $c) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                    @if (! $c->templates_count)
                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" class="inline" data-confirm="Delete the category “{{ $c->name }}”?">@csrf @method('DELETE')<button class="a-btn a-btn-ghost a-btn-sm text-rose-600">Delete</button></form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-12 text-center text-stone-500">No categories yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
