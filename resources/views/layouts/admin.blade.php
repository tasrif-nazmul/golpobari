<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e1712">
    <title>@yield('title', 'Dashboard') · GolpoBari Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600&family=Fraunces:opsz,wght,SOFT@9..144,600,50&display=swap">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh" x-data="{ nav: false }">
@php
    $admin = auth('admin')->user();
    $b = $adminBadges ?? [];
    $i = [
        'home' => 'M4 11l8-7 8 7M6 9.5V20h12V9.5M10 20v-5h4v5',
        'orders' => 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6',
        'pay' => 'M3 7h18v10H3zM3 11h18M7 15h3',
        'pages' => 'M6 3h9l5 5v13H6zM14 3v6h6M9 13h6M9 17h4',
        'more' => 'M4 7h16M4 12h16M4 17h16',
    ];
    $menu = [
        [null, null, [
            ['admin.dashboard', "Today's work", null, $i['home']],
        ]],
        ['Daily work', null, [
            ['admin.orders.index', 'Orders', $b['new'] ?? 0, $i['orders']],
            ['admin.payments.index', 'Payments', $b['payments'] ?? 0, $i['pay']],
            ['admin.pages.index', 'Customer pages', $b['production'] ?? 0, $i['pages']],
        ]],
        ['Shop', 'manage-catalog', [
            ['admin.templates.index', 'Templates', null, 'M4 5h16v14H4zM4 9h16M9 9v10'],
            ['admin.coupons.index', 'Coupons', null, 'M4 7h16v3a2 2 0 000 4v3H4v-3a2 2 0 000-4zM10 7v10'],
            ['admin.packages.index', 'Packages', null, 'M3 8l9-5 9 5-9 5zM3 8v8l9 5 9-5V8M12 13v8'],
            ['admin.categories.index', 'Categories', null, 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
        ]],
        ['Site content', 'manage-catalog', [
            ['admin.testimonials.index', 'Testimonials', null, 'M5 5h14v10H9l-4 4z'],
            ['admin.faqs.index', 'FAQs', null, 'M9 9a3 3 0 116 0c0 2-3 2-3 4M12 17h.01'],
            ['admin.content-pages.index', 'Policy pages', null, 'M7 3h10v18H7zM10 7h4M10 11h4'],
        ]],
        ['System', 'manage-settings', [
            ['admin.settings.edit', 'Settings', null, 'M12 15a3 3 0 100-6 3 3 0 000 6zM19 12l2-1-1-3-2 .5-1.5-1.5.5-2-3-1-1 2h-2l-1-2-3 1 .5 2L6 7.5 4 7 3 10l2 1v2l-2 1 1 3 2-.5 1.5 1.5-.5 2 3 1 1-2h2l1 2 3-1-.5-2 1.5-1.5 2 .5 1-3-2-1z'],
            ['admin.activity.index', 'Activity log', null, 'M12 8v4l3 2M12 21a9 9 0 110-18 9 9 0 010 18z'],
            ['admin.admins.index', 'Admins', null, 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21a8 8 0 0116 0'],
        ]],
    ];
    $isActive = fn ($route) => request()->routeIs($route) || ($route !== 'admin.dashboard' && request()->routeIs(str_replace('.index', '', $route).'*'))
        || ($route === 'admin.pages.index' && request()->routeIs('admin.pages.*'));
    $tabs = [
        ['admin.dashboard', 'Today', $i['home'], 0],
        ['admin.orders.index', 'Orders', $i['orders'], 0],
        ['admin.payments.index', 'Payments', $i['pay'], $b['payments'] ?? 0],
        ['admin.pages.index', 'Pages', $i['pages'], $b['production'] ?? 0],
    ];
@endphp

<div class="flex min-h-dvh">
    {{-- Sidebar (desktop), slide-over menu (phone) --}}
    <aside class="fixed inset-y-0 left-0 z-50 flex w-72 -translate-x-full flex-col bg-stone-950 text-stone-300 transition-transform duration-200 lg:w-64 lg:translate-x-0" :class="nav && 'translate-x-0'">
        <div class="pointer-events-none absolute inset-y-3 right-1.5 border-r-[1.5px] border-dashed border-white/10"></div>
        <div class="flex items-center justify-between px-5 py-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 text-stone-50">
                <x-logo class="h-8 w-8" />
                <span><span class="a-title block text-lg leading-none">GolpoBari</span><span class="text-[11px] uppercase tracking-[.18em] text-stone-500">Office</span></span>
            </a>
            <button class="rounded-lg p-2 text-stone-400 hover:bg-white/5 hover:text-white lg:hidden" @click="nav = false" aria-label="Close menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <nav class="flex-1 space-y-5 overflow-y-auto px-3 pb-6">
            @foreach ($menu as [$group, $ability, $items])
                @if (! $ability || Gate::allows($ability))
                    <div>
                        @if ($group)<p class="px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-[.14em] text-stone-500">{{ $group }}</p>@endif
                        @foreach ($items as [$route, $label, $badge, $icon])
                            @php $active = $isActive($route); @endphp
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif class="relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] font-medium transition lg:py-2 lg:text-sm {{ $active ? 'bg-white/[.07] text-white' : 'hover:bg-white/[.04] hover:text-white' }}">
                                @if ($active)<span class="absolute inset-y-2 left-0 w-[3px] rounded-full bg-holud"></span>@endif
                                <svg class="h-[18px] w-[18px] shrink-0 {{ $active ? 'text-holud' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                                <span class="flex-1">{{ $label }}</span>
                                @if ($badge)<span class="rounded-full bg-brand px-2 py-0.5 text-[11px] font-bold tabular-nums text-white">{{ $badge }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </nav>
        <div class="border-t border-dashed border-white/10 px-5 py-4 text-sm">
            <a href="{{ url('/') }}" target="_blank" class="flex items-center gap-2 text-stone-400 hover:text-white">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/></svg>Open the website
            </a>
        </div>
    </aside>
    <div class="fixed inset-0 z-40 bg-stone-950/50 lg:hidden" x-show="nav" x-transition.opacity x-cloak @click="nav = false"></div>

    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-30 border-b border-stone-200 bg-paper/90 backdrop-blur" x-data="{ s: false }">
            <div class="flex h-14 items-center gap-2 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 lg:hidden" x-show="!s">
                    <x-logo class="h-7 w-7 text-stone-900" />
                    <span class="a-title text-lg">GolpoBari</span>
                </a>
                <form action="{{ route('admin.orders.index') }}" method="GET" class="relative flex-1 max-w-md" :class="s ? 'block' : 'hidden sm:block'">
                    <label class="sr-only" for="global-q">Search orders</label>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input id="global-q" name="q" x-ref="q" value="{{ request()->routeIs('admin.orders.index') ? request('q') : '' }}" class="a-input !min-h-9 !rounded-full !py-1.5 !pl-9" placeholder="Search order, name, phone, TrxID…">
                </form>
                <div class="ml-auto flex items-center gap-1">
                    <button type="button" class="rounded-lg p-2 text-stone-600 hover:bg-stone-200/60 sm:hidden" @click="s = !s; $nextTick(() => s && $refs.q.focus())" :aria-label="s ? 'Close search' : 'Search'">
                        <svg x-show="!s" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                        <svg x-show="s" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                    <div class="relative" x-data="{ o: false }" @click.outside="o = false">
                        <button class="flex items-center gap-2 rounded-full p-1 pr-1 text-sm font-medium hover:bg-stone-200/60 sm:pr-3" @click="o = !o">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-stone-900 text-xs font-bold text-white">{{ strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                            <span class="hidden sm:block">{{ $admin->name }}</span>
                        </button>
                        <div x-show="o" x-cloak x-transition.origin.top.right class="absolute right-0 mt-1 w-48 overflow-hidden rounded-xl bg-card py-1 shadow-lg ring-1 ring-stone-200">
                            <a href="{{ route('admin.account.edit') }}" class="block px-4 py-2.5 text-sm hover:bg-stone-50">My account</a>
                            <a href="{{ url('/') }}" target="_blank" class="block px-4 py-2.5 text-sm hover:bg-stone-50">Open the website</a>
                            <form method="POST" action="{{ route('admin.logout') }}" class="border-t border-dashed border-stone-200">@csrf<button class="block w-full px-4 py-2.5 text-left text-sm hover:bg-stone-50">Log out</button></form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 pt-5 pb-28 sm:px-6 lg:px-8 lg:pt-7 lg:pb-12">
            <div class="mx-auto max-w-7xl">
                @if (session('success'))
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 ring-1 ring-emerald-200" x-data="{ v: true }" x-show="v">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>
                        <span class="flex-1 break-words">{{ session('success') }}</span>
                        <button type="button" class="text-emerald-700/60 hover:text-emerald-900" @click="v = false" aria-label="Dismiss">✕</button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-5 flex items-start gap-2.5 rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900 ring-1 ring-rose-200">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 8v5M12 16.5v.01M12 3l9.5 17h-19z"/></svg>
                        <span class="flex-1 break-words">{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any() && ! ($hideErrorSummary ?? false))
                    <div class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-900 ring-1 ring-rose-200">
                        <p class="font-semibold">Please fix the following:</p>
                        <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- Phone tab bar: the four places used every day, plus the full menu. --}}
<nav class="pb-safe fixed inset-x-0 bottom-0 z-30 border-t border-stone-200 bg-card/95 backdrop-blur lg:hidden" aria-label="Main">
    <div class="mx-auto grid max-w-md grid-cols-5">
        @foreach ($tabs as $t)
            @php [$route, $label, $icon, $count] = $t; $active = $isActive($route); @endphp
            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif class="relative flex flex-col items-center gap-0.5 pt-2.5 pb-2 text-[11px] font-semibold {{ $active ? 'text-brand' : 'text-stone-500' }}">
                @if ($active)<span class="absolute top-0 h-[3px] w-8 rounded-b-full bg-brand"></span>@endif
                <span class="relative">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $active ? 2 : 1.7 }}" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                    @if ($count)<span class="absolute -right-2.5 -top-1.5 min-w-[18px] rounded-full bg-brand px-1 text-center text-[10px] font-bold leading-[18px] text-white ring-2 ring-card">{{ $count }}</span>@endif
                </span>
                {{ $label }}
            </a>
        @endforeach
        <button type="button" class="flex flex-col items-center gap-0.5 pt-2.5 pb-2 text-[11px] font-semibold text-stone-500" @click="nav = true">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"><path d="{{ $i['more'] }}"/></svg>
            More
        </button>
    </div>
</nav>

{{-- Confirm dialog --}}
<div x-data="confirmHost" x-show="open" x-cloak class="fixed inset-0 z-[60] flex items-end justify-center bg-stone-950/50 p-3 sm:items-center sm:p-4" @keydown.escape.window="cancel()" role="dialog" aria-modal="true">
    <div class="w-full max-w-sm rounded-2xl bg-card p-5 shadow-xl" @click.outside="cancel()" x-show="open" x-transition>
        <p class="a-title text-lg">Please confirm</p>
        <p class="mt-1 text-sm text-stone-600" x-text="message"></p>
        <div class="mt-5 grid grid-cols-2 gap-2 sm:flex sm:justify-end">
            <button type="button" class="a-btn a-btn-ghost" @click="cancel()">Cancel</button>
            <button type="button" class="a-btn" :class="danger ? 'a-btn-danger' : 'a-btn-success'" x-ref="ok" @click="ok()">Yes, do it</button>
        </div>
    </div>
</div>

{{-- Small "Copied" toast used by copy buttons --}}
<div x-data="{ show: false, msg: '' }" @toast.window="msg = $event.detail; show = true; clearTimeout(window.__t); window.__t = setTimeout(() => show = false, 1800)" x-show="show" x-cloak x-transition.opacity
     class="pointer-events-none fixed inset-x-0 bottom-24 z-[70] flex justify-center lg:bottom-8">
    <span class="rounded-full bg-stone-900 px-4 py-2 text-sm font-medium text-white shadow-lg" x-text="msg"></span>
</div>
</body>
</html>
