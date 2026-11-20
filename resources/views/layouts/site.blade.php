<!DOCTYPE html>
<html lang="bn" class="scroll-pt-20">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#fbf5ec">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-seo :seo="$seo ?? []" />
    <x-favicon />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Caveat:wght@600&family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,700;1,9..144,500&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@500;700;800&display=swap">
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
                    <span class="block font-display text-[1.35rem] font-bold">{{ $siteName }}</span>
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
<footer class="relative mt-10 bg-ink text-paper/80">
    <div class="stitch-line text-paper/60"></div>
    <div class="container-x grid gap-10 px-4 py-14 sm:px-6 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
            <div class="flex items-center gap-2.5 text-paper">
                <x-logo class="h-10 w-10" />
                <span class="font-display text-2xl font-bold">{{ $siteName }}</span>
            </div>
            <p class="mt-4 max-w-xs font-display text-lg text-paper">{{ $settings->get('tagline') }}</p>
            <p class="mt-1 font-serif text-sm italic text-paper/60">{{ $settings->get('tagline_en') }}</p>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest text-paper/50">গল্পবাড়ি</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a class="hover:text-holud" href="{{ route('templates.index') }}">টেমপ্লেট</a></li>
                <li><a class="hover:text-holud" href="{{ route('pricing') }}">প্যাকেজ ও দাম</a></li>
                <li><a class="hover:text-holud" href="{{ route('about') }}">আমাদের কথা</a></li>
                <li><a class="hover:text-holud" href="{{ route('track') }}">অর্ডার ট্র্যাক</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest text-paper/50">সাহায্য</h2>
            <ul class="mt-4 space-y-2.5">
                <li><a class="hover:text-holud" href="{{ route('faq') }}">প্রশ্নোত্তর</a></li>
                <li><a class="hover:text-holud" href="{{ route('contact') }}">যোগাযোগ</a></li>
                <li><a class="hover:text-holud" href="{{ route('terms') }}">শর্তাবলি</a></li>
                <li><a class="hover:text-holud" href="{{ route('privacy') }}">প্রাইভেসি</a></li>
                <li><a class="hover:text-holud" href="{{ route('refund') }}">রিফান্ড নীতি</a></li>
            </ul>
        </div>
        <div>
            <h2 class="font-sans text-sm font-semibold uppercase tracking-widest text-paper/50">যোগাযোগ</h2>
            <ul class="mt-4 space-y-2.5">
                @if ($p = $settings->get('contact_phone'))<li><a class="hover:text-holud" href="tel:{{ preg_replace('/[^0-9+]/', '', $p) }}">{{ $p }}</a></li>@endif
                @if ($e = $settings->get('contact_email'))<li><a class="hover:text-holud break-all" href="mailto:{{ $e }}">{{ $e }}</a></li>@endif
                <li class="text-paper/60">{{ $settings->get('office_hours') }}</li>
            </ul>
            <x-social-links class="mt-5" />
        </div>
    </div>
    <div class="border-t border-paper/10">
        <div class="container-x flex flex-col items-center justify-between gap-2 px-4 py-5 text-sm text-paper/50 sm:flex-row sm:px-6">
            <p>© {{ \App\Support\Bn::digits(now()->year) }} {{ $settings->get('site_name') }}. সব গল্প সংরক্ষিত।</p>
            <p>বাংলাদেশে ভালোবাসা দিয়ে তৈরি 🇧🇩</p>
        </div>
    </div>
</footer>
@endunless

@stack('sticky')
@stack('scripts')
</body>
</html>
