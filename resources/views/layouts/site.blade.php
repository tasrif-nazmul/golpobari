<!DOCTYPE html>
<html lang="bn" class="scroll-pt-20">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#fcf6ec">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo :seo="$seo ?? []" />
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Galada&family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,700;1,9..144,500&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;700;800&display=swap">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-dvh grain flex flex-col">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-4 focus:py-2 focus:text-paper">মূল অংশে যান</a>

@php
    $nav = [
        ['route' => 'templates.index', 'label' => 'টেমপ্লেট'],
        ['route' => 'categories.index', 'label' => 'ক্যাটাগরি'],
        ['route' => 'pricing', 'label' => 'প্যাকেজ'],
        ['route' => 'faq', 'label' => 'প্রশ্নোত্তর'],
        ['route' => 'track', 'label' => 'অর্ডার ট্র্যাক'],
    ];
    $siteName = $settings->get('site_name_bn', 'গল্পবাড়ি');
    $logo = $settings->fileUrl('logo_path');
@endphp

<header x-data="{ open: false, scrolled: false }" @scroll.window.passive="scrolled = window.scrollY > 8"
        :class="scrolled ? 'bg-paper/90 backdrop-blur shadow-[0_1px_0_rgb(31_26_22/0.08)]' : 'bg-transparent'"
        class="sticky top-0 z-50 transition-colors {{ $headerClass ?? '' }}">
    <div class="container-x flex h-16 items-center justify-between gap-4 px-4 sm:px-6 md:h-[4.5rem]">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-ink" aria-label="{{ $settings->get('site_name') }} হোম">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $settings->get('site_name') }}" class="h-9 w-auto" width="120" height="36">
            @else
                <x-logo class="h-9 w-9 text-ink" />
                <span class="leading-none">
                    <span class="block font-fancy text-[1.6rem] leading-none">{{ $siteName }}</span>
                    <span class="block font-serif text-[0.7rem] italic tracking-wide text-ink-mute">{{ $settings->get('site_name') }}</span>
                </span>
            @endif
        </a>

        <nav class="hidden items-center gap-1 lg:flex" aria-label="মূল মেনু">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="rounded-full px-4 py-2 font-medium text-ink-soft transition hover:bg-paper-2 hover:text-ink {{ request()->routeIs($item['route'].'*') ? 'bg-paper-2 text-ink' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('order.create') }}" class="btn btn-primary btn-sm hidden sm:inline-flex">শুরু করুন</a>
            <button type="button" class="inline-flex h-11 w-11 items-center justify-center rounded-full ring-1 ring-ink/15 lg:hidden"
                    @click="open = !open" :aria-expanded="open" aria-controls="mobile-menu" aria-label="মেনু">
                <svg x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h10" stroke-linecap="round"/></svg>
                <svg x-show="open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6 6 18" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" x-show="open" x-cloak x-transition.opacity @keydown.escape.window="open = false"
         class="border-t border-ink/10 bg-paper px-4 pb-6 pt-2 lg:hidden">
        <nav class="flex flex-col" aria-label="মোবাইল মেনু">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" class="flex items-center justify-between border-b border-dashed border-ink/15 py-4 text-lg font-medium">
                    {{ $item['label'] }} <span aria-hidden="true" class="text-ink-mute">→</span>
                </a>
            @endforeach
            <a href="{{ route('contact') }}" class="flex items-center justify-between border-b border-dashed border-ink/15 py-4 text-lg font-medium">যোগাযোগ <span aria-hidden="true" class="text-ink-mute">→</span></a>
        </nav>
        <a href="{{ route('order.create') }}" class="btn btn-primary mt-6 w-full">আপনার গল্প শুরু করুন</a>
    </div>
</header>

<main id="main" class="flex-1">
    @yield('content')
</main>

@unless ($hideFooter ?? false)
<footer class="night relative mt-16 overflow-hidden">
    <div class="stars opacity-60"></div>
    <svg aria-hidden="true" class="spin-slow pointer-events-none absolute -right-40 -top-40 h-[30rem] w-[30rem] text-gold/15" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="1">
        @for ($i = 0; $i < 16; $i++)<ellipse cx="100" cy="52" rx="13" ry="40" transform="rotate({{ $i * 22.5 }} 100 100)"/>@endfor
        <circle cx="100" cy="100" r="28"/><circle cx="100" cy="100" r="96" stroke-dasharray="3 6"/>
    </svg>
    <div class="container-x relative grid gap-10 px-4 pb-12 pt-16 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
            <div class="flex items-center gap-2.5 text-paper">
                <x-logo class="h-10 w-10" />
                <span class="font-fancy text-3xl">{{ $siteName }}</span>
            </div>
            <p class="mt-4 max-w-xs font-display text-lg text-paper">{{ $settings->get('tagline') }}</p>
            <p class="mt-1 font-serif text-sm italic text-paper/60">{{ $settings->get('tagline_en') }}</p>
            <a href="{{ route('order.create') }}" class="btn btn-gold btn-sm mt-6">আপনার গল্প শুরু করুন <x-icon name="arrow-right" class="h-4 w-4" /></a>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest !text-gold">গল্পবাড়ি</h2>
            <ul class="mt-4 space-y-2.5 text-paper/80">
                <li><a class="hover:text-holud" href="{{ route('templates.index') }}">টেমপ্লেট</a></li>
                <li><a class="hover:text-holud" href="{{ route('pricing') }}">প্যাকেজ ও দাম</a></li>
                <li><a class="hover:text-holud" href="{{ route('about') }}">আমাদের কথা</a></li>
                <li><a class="hover:text-holud" href="{{ route('track') }}">অর্ডার ট্র্যাক</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest !text-gold">সাহায্য</h2>
            <ul class="mt-4 space-y-2.5 text-paper/80">
                <li><a class="hover:text-holud" href="{{ route('faq') }}">প্রশ্নোত্তর</a></li>
                <li><a class="hover:text-holud" href="{{ route('contact') }}">যোগাযোগ</a></li>
                <li><a class="hover:text-holud" href="{{ route('terms') }}">শর্তাবলি</a></li>
                <li><a class="hover:text-holud" href="{{ route('privacy') }}">প্রাইভেসি</a></li>
                <li><a class="hover:text-holud" href="{{ route('refund') }}">রিফান্ড নীতি</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest !text-gold">যোগাযোগ</h2>
            <ul class="mt-4 space-y-2.5 text-paper/80">
                @if ($p = $settings->get('contact_phone'))<li><a class="hover:text-holud" href="tel:{{ preg_replace('/[^0-9+]/', '', $p) }}">{{ $p }}</a></li>@endif
                @if ($e = $settings->get('contact_email'))<li><a class="hover:text-holud break-all" href="mailto:{{ $e }}">{{ $e }}</a></li>@endif
                <li class="text-paper/60">{{ $settings->get('office_hours') }}</li>
            </ul>
            <x-social-links class="mt-5" />
        </div>
    </div>
    <div class="relative border-t border-white/10">
        <div class="container-x flex flex-col items-center justify-between gap-2 px-4 py-5 text-sm text-paper/50 sm:flex-row sm:px-6">
            <p>© {{ \App\Support\Bn::digits(now()->year) }} {{ $settings->get('site_name') }}. সব গল্প সংরক্ষিত।</p>
            <p>বাংলাদেশে ভালোবাসা দিয়ে তৈরি 🇧🇩</p>
        </div>
    </div>
</footer>
@endunless

{{-- Live template preview popup: any button can open it with $dispatch('tpl-preview', {...}) --}}
<div x-data="tplPreview" @tpl-preview.window="open($event.detail)" @keydown.escape.window="close()">
    <template x-if="item">
        <div class="fixed inset-0 z-[80] flex items-stretch justify-center bg-night/80 backdrop-blur-md sm:items-center sm:p-6" role="dialog" aria-modal="true" :aria-label="item.name + ' প্রিভিউ'" @click.self="close()" x-transition.opacity>
            <div class="relative flex h-full w-full flex-col sm:h-auto sm:w-auto sm:flex-row sm:items-center sm:gap-10">
                <div class="flex items-center justify-between gap-3 bg-night px-4 py-3 text-paper sm:hidden">
                    <p class="min-w-0 truncate font-semibold" x-text="item.name"></p>
                    <button type="button" class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-white/10" @click="close()" aria-label="বন্ধ করুন"><x-icon name="x" class="h-5 w-5" /></button>
                </div>
                <div class="relative min-h-0 flex-1 bg-white sm:flex-none sm:bg-transparent">
                    <div class="pv-frame h-full">
                        <div class="pv-screen relative h-full">
                            <div x-show="loading" class="absolute inset-0 z-10 grid place-items-center bg-paper text-ink-mute"><span class="animate-pulse">প্রিভিউ আসছে…</span></div>
                            <iframe :src="item.render" class="block h-full w-full border-0 bg-white" :title="item.name + ' লাইভ প্রিভিউ'" allow="autoplay" @load="loading = false"></iframe>
                        </div>
                    </div>
                </div>
                <div class="border-t border-ink/10 bg-paper px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 sm:w-80 sm:rounded-[1.75rem] sm:border-0 sm:p-7 sm:shadow-[var(--shadow-lift)]">
                    <div class="hidden sm:block">
                        <p class="text-sm font-semibold text-ink-mute" x-text="item.category"></p>
                        <h2 class="mt-1 font-display text-3xl font-bold leading-tight" x-text="item.name"></h2>
                        <p class="mt-3 text-[0.95rem] leading-relaxed text-ink-soft" x-text="item.description"></p>
                        <ul class="mt-5 space-y-2 text-sm text-ink-soft">
                            <li x-show="item.music" class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-full bg-night text-holud"><span class="flex h-3 items-end gap-[2px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span></span> গান: <b class="text-ink" x-text="item.music"></b></li>
                            <li class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-full bg-holud/30">👆</span> ছুঁয়ে, স্ক্রল করে দেখুন: এটা আসল পেজ</li>
                            <li class="flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-full bg-holud/30">✍️</span> নাম, লেখা আর ছবি হবে আপনার</li>
                        </ul>
                    </div>
                    <div class="flex gap-2 sm:mt-7 sm:flex-col">
                        <a :href="item.order" class="btn btn-primary flex-1 sm:w-full">এটা দিয়ে বানাই <x-icon name="arrow-right" class="h-5 w-5" /></a>
                        <a :href="item.show" class="btn btn-ghost hidden sm:inline-flex sm:w-full">বিস্তারিত দেখুন</a>
                    </div>
                </div>
                <button type="button" class="absolute -right-3 -top-3 hidden h-11 w-11 place-items-center rounded-full bg-white text-ink shadow-lg sm:grid" @click="close()" aria-label="বন্ধ করুন"><x-icon name="x" class="h-5 w-5" /></button>
            </div>
        </div>
    </template>
</div>

@stack('sticky')
@stack('scripts')
</body>
</html>
