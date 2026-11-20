@props(['title', 'sub' => null, 'back' => null])
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if ($back)<a href="{{ $back }}" class="mb-1 inline-flex items-center gap-1 text-sm font-medium text-stone-500 hover:text-stone-900"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>Back</a>@endif
        <h1 class="a-title truncate text-2xl sm:text-3xl">{{ $title }}</h1>
        @if ($sub)<p class="mt-0.5 text-sm text-stone-500">{{ $sub }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
</div>
