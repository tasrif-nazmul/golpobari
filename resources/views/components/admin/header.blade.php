@props(['title', 'sub' => null, 'back' => null])
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        @if ($back)<a href="{{ $back }}" class="text-sm font-medium text-stone-500 hover:text-stone-900">← Back</a>@endif
        <h1 class="text-2xl font-bold tracking-tight">{{ $title }}</h1>
        @if ($sub)<p class="mt-0.5 text-sm text-stone-500">{{ $sub }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap gap-2">{{ $actions }}</div>@endisset
</div>
