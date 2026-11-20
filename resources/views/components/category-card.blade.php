@props(['category', 'index' => 0])
@php
    $accent = $category->accent_color ?: '#d9482b';
    $tilt = [-1.2, 0.8, -0.6, 1.1, -0.9, 0.6, -1][$index % 7];
@endphp
<a href="{{ route('categories.show', $category) }}"
   {{ $attributes->merge(['class' => 'group stitch relative flex min-h-[11rem] flex-col justify-between overflow-hidden rounded-[var(--radius-card)] p-5 transition duration-300 hover:-translate-y-1 hover:rotate-0 sm:p-6']) }}
   style="background: color-mix(in oklab, {{ $accent }} 13%, white); color: {{ $accent }}; transform: rotate({{ $tilt }}deg)">
    <div class="flex items-start justify-between">
        @if ($category->iconUrl())
            <img src="{{ $category->iconUrl() }}" alt="" class="h-12 w-12 rounded-xl object-cover" loading="lazy" width="48" height="48">
        @else
            <span class="text-4xl transition duration-300 group-hover:scale-110" aria-hidden="true">{{ $category->emoji }}</span>
        @endif
        <span class="rounded-full bg-white/70 px-2.5 py-0.5 text-xs font-semibold text-ink-soft">{{ \App\Support\Bn::digits($category->active_templates_count ?? $category->activeTemplates()->count()) }} টেমপ্লেট</span>
    </div>
    <div class="relative z-10 text-ink">
        <h3 class="text-xl font-bold sm:text-2xl">{{ $category->displayName() }}</h3>
        <p class="mt-1 text-[0.95rem] leading-snug text-ink-soft">{{ $category->tagline }}</p>
    </div>
</a>
