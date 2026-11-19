@extends('layouts.admin')
@section('title', 'FAQ')
@section('content')
<x-admin.header title="FAQ" sub="Shown on the homepage and the FAQ page, in this order.">
    <x-slot:actions><a href="{{ route('admin.faqs.create') }}" class="a-btn a-btn-primary">+ Add question</a></x-slot:actions>
</x-admin.header>
<div class="a-card divide-y divide-stone-100">
    @forelse ($items as $f)
        <div class="flex items-start gap-3 p-4">
            @include('admin.partials.reorder', ['type' => 'faqs', 'id' => $f->id, 'first' => $loop->first, 'last' => $loop->last])
            <div class="min-w-0 flex-1">
                <div class="font-semibold">{{ $f->question }} @unless ($f->is_active)<x-admin.badge>Hidden</x-admin.badge>@endunless</div>
                <p class="mt-1 line-clamp-2 text-sm text-stone-600">{{ $f->answer }}</p>
            </div>
            <div class="flex shrink-0 gap-1.5">
                <a href="{{ route('admin.faqs.edit', $f) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.faqs.destroy', $f) }}" data-confirm="Delete this question?">@csrf @method('DELETE')<button class="a-btn a-btn-ghost a-btn-sm text-rose-600">Delete</button></form>
            </div>
        </div>
    @empty
        <p class="p-12 text-center text-stone-500">No questions yet.</p>
    @endforelse
</div>
@endsection
