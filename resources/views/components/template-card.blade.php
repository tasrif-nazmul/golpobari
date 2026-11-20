@props(['template', 'compact' => false])
@php
    $theme = $template->resolvedTheme();
    $shots = $template->phoneShots();
    $preview = $template->previewUrl();
    $payload = $template->previewPayload();
    $tint = $template->category?->accent_color ?: ($theme['accent'] ?? '#e3ad4a');
@endphp
<article x-data {{ $attributes->merge(['class' => 'tcard group']) }} style="--tint: color-mix(in oklab, {{ $tint }} 22%, #fbf3e6)">
    <div class="tcard__stage">
        <span class="absolute left-4 top-4 z-10 rounded-full bg-white/85 px-3 py-1 text-xs font-bold text-ink shadow-sm backdrop-blur">{{ $payload['category'] }}</span>
        @if ($payload['music'])
            <span class="absolute right-4 top-4 z-10 inline-flex items-center gap-1.5 rounded-full bg-night/85 px-2.5 py-1 text-[11px] font-semibold text-paper backdrop-blur" title="এই টেমপ্লেটে গান আছে">
                <span class="flex h-3 items-end gap-[2px] text-holud"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span> গান
            </span>
        @endif
        <button type="button" class="absolute inset-0 z-[5] cursor-zoom-in" @click="$dispatch('tpl-preview', @js($payload))" aria-label="{{ $template->name }}: লাইভ প্রিভিউ খুলুন"></button>
        @if ($shots)
            <div class="phone phone--sm tcard__back" aria-hidden="true"><div class="phone__screen"><img src="{{ $shots['inside'] }}" alt="" loading="lazy" decoding="async" width="390" height="800"></div></div>
            <div class="phone phone--sm tcard__front"><div class="phone__screen"><img src="{{ $shots['cover'] }}" alt="{{ $template->name }} টেমপ্লেট" loading="lazy" decoding="async" width="390" height="800"></div></div>
        @else
            <div class="phone phone--sm phone--solo"><div class="phone__screen" style="background: {{ $theme['background'] ?? '#fff' }}">
                @if ($preview)<img src="{{ $preview }}" alt="{{ $template->name }} টেমপ্লেট" loading="lazy" decoding="async">@else<x-template-mock :layout="$template->layout" :theme="$theme" />@endif
            </div></div>
        @endif
        <span class="tcard__play pointer-events-none inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-bold text-ink opacity-0 shadow-lg transition duration-300 group-hover:opacity-100">
            <svg class="h-3.5 w-3.5 text-sindoor" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg> লাইভ দেখুন
        </span>
    </div>
    <div class="flex flex-1 flex-col px-5 pb-5 pt-4">
        <h3 class="font-display text-[1.35rem] font-bold leading-snug">{{ $template->name }}</h3>
        @unless ($compact)
            <p class="mt-1 line-clamp-2 text-[0.95rem] leading-relaxed text-ink-soft">{{ $template->tagline ?: $template->description }}</p>
        @endunless
        <div class="mt-auto grid grid-cols-2 gap-2 pt-4">
            <button type="button" class="btn btn-ghost btn-sm" @click="$dispatch('tpl-preview', @js($payload))">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg> প্রিভিউ
            </button>
            <a href="{{ route('templates.show', $template) }}" class="btn btn-ghost btn-sm">বিস্তারিত</a>
            <a href="{{ $payload['order'] }}" class="btn btn-ink btn-sm col-span-2">এটা দিয়ে বানাই <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
    </div>
</article>
