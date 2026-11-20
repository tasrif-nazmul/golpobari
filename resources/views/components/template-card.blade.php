@props(['template', 'compact' => false])
@php
    $theme = $template->resolvedTheme();
    $preview = $template->previewUrl();
    $fakeSlug = ['romantic-story' => 'rafi-mitu', 'birthday-celebration' => 'happy-birthday-mitu', 'love-letter' => 'for-nusrat', 'our-journey' => 'anika-shuvo', 'clean-business' => 'rafis-cafe', 'modern-service' => 'sakib', 'photo-memories' => 'sajek-2025', 'memory-timeline' => 'our-story', 'minimal-portfolio' => 'nodi', 'modern-professional' => 'tanvir', 'creative-portfolio' => 'ritu', 'event-invitation' => 'anika-weds-shuvo', 'proposal' => 'nila-will-you', 'gift-surprise' => 'surprise-tithi', 'reasons-cards' => 'reasons-for-ammu', 'travel-diary' => 'bandarban-2025', 'tribute' => 'in-memory-of-abbu', 'festival-greeting' => 'eid-mubarak-sakib'][$template->layout] ?? $template->slug;
@endphp
<article {{ $attributes->merge(['class' => 'group card flex flex-col overflow-hidden transition duration-300 hover:-translate-y-1 hover:shadow-[var(--shadow-lift)]']) }}>
    <a href="{{ route('templates.show', $template) }}" class="block" aria-label="{{ $template->name }} টেমপ্লেট দেখুন">
        <div class="flex items-center gap-1.5 border-b border-ink/5 bg-paper-2/60 px-3 py-2">
            <span class="h-2 w-2 rounded-full bg-sindoor/70"></span><span class="h-2 w-2 rounded-full bg-holud/80"></span><span class="h-2 w-2 rounded-full bg-kolapata/60"></span>
            <span class="ml-2 truncate rounded-full bg-white px-2.5 py-0.5 text-[11px] text-ink-mute">golpobari.com/<b class="font-semibold text-ink">{{ $fakeSlug }}</b></span>
        </div>
        <div class="relative aspect-[4/5] overflow-hidden" style="background: {{ $theme['background'] ?? '#fff' }}">
            @if ($preview)
                <img src="{{ $preview }}" alt="{{ $template->name }} টেমপ্লেটের প্রিভিউ" loading="lazy" decoding="async" width="600" height="750"
                     class="h-full w-full object-cover object-top transition duration-700 group-hover:scale-[1.03]">
            @else
                <x-template-mock :layout="$template->layout" :theme="$theme" />
            @endif
        </div>
    </a>
    <div class="flex flex-1 flex-col p-4 sm:p-5">
        <p class="text-sm font-medium text-ink-mute">{{ $template->category?->emoji }} {{ $template->category?->displayName() }}</p>
        <h3 class="mt-1 font-serif text-xl font-semibold">{{ $template->name }}</h3>
        @unless ($compact)
            <p class="mt-1 line-clamp-2 text-[0.95rem] leading-relaxed text-ink-soft">{{ $template->tagline ?: $template->description }}</p>
        @endunless
        <div class="mt-auto flex gap-2 pt-4">
            <a href="{{ route('templates.show', $template) }}" class="btn btn-ghost btn-sm flex-1">দেখুন</a>
            <a href="{{ route('order.create', ['template' => $template->slug]) }}" class="btn btn-ink btn-sm flex-1">এটা দিয়ে বানাই</a>
        </div>
    </div>
</article>
