@extends('layouts.admin')
@section('title', 'Testimonials')
@section('content')
<x-admin.header title="Testimonials" sub="Only add real customer words, with their permission. The homepage section hides itself when none are active.">
    <x-slot:actions><a href="{{ route('admin.testimonials.create') }}" class="a-btn a-btn-primary">+ Add testimonial</a></x-slot:actions>
</x-admin.header>
<div class="a-card divide-y divide-stone-100">
    @forelse ($items as $t)
        <div class="flex items-start gap-3 p-4">
            @include('admin.partials.reorder', ['type' => 'testimonials', 'id' => $t->id, 'first' => $loop->first, 'last' => $loop->last])
            @if ($t->avatarUrl())<img src="{{ $t->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover">@else<span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-stone-100 font-semibold text-stone-500">{{ mb_substr($t->name, 0, 1) }}</span>@endif
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2"><span class="font-semibold">{{ $t->name }}</span><span class="text-xs text-stone-500">{{ $t->role }}</span><span class="text-xs text-amber-500">{{ str_repeat('★', $t->rating) }}</span>@unless ($t->is_active)<x-admin.badge>Hidden</x-admin.badge>@endunless</div>
                <p class="mt-1 line-clamp-2 text-sm text-stone-600">{{ $t->quote }}</p>
            </div>
            <div class="flex shrink-0 gap-1.5">
                <a href="{{ route('admin.testimonials.edit', $t) }}" class="a-btn a-btn-ghost a-btn-sm">Edit</a>
                <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" data-confirm="Delete this testimonial?">@csrf @method('DELETE')<button class="a-btn a-btn-ghost a-btn-sm text-rose-600">Delete</button></form>
            </div>
        </div>
    @empty
        <p class="p-12 text-center text-stone-500">No testimonials yet.</p>
    @endforelse
</div>
@endsection
