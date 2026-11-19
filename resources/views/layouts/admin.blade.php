<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · GolpoBari Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Hind+Siliguri:wght@400;500;600&display=swap">
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh" x-data="{ nav: false }">
@php
    $admin = auth('admin')->user();
    $b = $adminBadges ?? [];
    $menu = [
        ['Overview', null, [
            ['admin.dashboard', 'Dashboard', null, 'M3 12l9-9 9 9M5 10v10h14V10'],
        ]],
        ['Operations', null, [
            ['admin.orders.index', 'Orders', $b['new'] ?? 0, 'M4 6h16M4 12h16M4 18h10'],
            ['admin.payments.index', 'Payments', $b['payments'] ?? 0, 'M3 7h18v10H3zM3 11h18'],
            ['admin.pages.index', 'Customer pages', $b['production'] ?? 0, 'M6 3h9l5 5v13H6zM14 3v6h6'],
        ]],
        ['Catalogue', 'manage-catalog', [
            ['admin.categories.index', 'Categories', null, 'M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z'],
            ['admin.templates.index', 'Templates', null, 'M4 5h16v14H4zM4 9h16'],
            ['admin.packages.index', 'Packages', null, 'M3 8l9-5 9 5-9 5zM3 8v8l9 5 9-5V8'],
            ['admin.coupons.index', 'Coupons', null, 'M4 7h16v4a2 2 0 000 4v4H4v-4a2 2 0 000-4z'],
        ]],
        ['Content', 'manage-catalog', [
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
@endphp

<div class="flex min-h-dvh">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto bg-stone-950 text-stone-300 transition-transform lg:translate-x-0" :class="nav && 'translate-x-0'">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-5 py-5 text-white">
            <x-logo class="h-8 w-8" />
            <span><span class="block text-base font-bold leading-none">GolpoBari</span><span class="text-xs text-stone-500">Admin</span></span>
        </a>
        <nav class="space-y-6 px-3 pb-8">
            @foreach ($menu as [$group, $ability, $items])
                @if (! $ability || Gate::allows($ability))
                    <div>
                        <p class="px-3 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-stone-500">{{ $group }}</p>
                        @foreach ($items as [$route, $label, $badge, $icon])
                            @php $active = request()->routeIs(str_replace('.index', '', $route).'*') || request()->routeIs($route); @endphp
                            <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                                <svg class="h-4.5 w-4.5 h-[18px] w-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"/></svg>
                                <span class="flex-1">{{ $label }}</span>
                                @if ($badge)<span class="rounded-full bg-brand px-2 py-0.5 text-[11px] font-bold text-white">{{ $badge }}</span>@endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </nav>
    </aside>
    <div class="fixed inset-0 z-30 bg-black/40 lg:hidden" x-show="nav" x-cloak @click="nav = false"></div>

    <div class="flex min-w-0 flex-1 flex-col lg:pl-64">
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-stone-200 bg-white/90 px-4 backdrop-blur sm:px-6">
            <button class="a-btn a-btn-ghost lg:hidden" @click="nav = true" aria-label="Menu">☰</button>
            <form action="{{ route('admin.orders.index') }}" method="GET" class="flex-1 max-w-md">
                <label class="sr-only" for="global-q">Search orders</label>
                <input id="global-q" name="q" value="{{ request()->routeIs('admin.orders.index') ? request('q') : '' }}" class="a-input !min-h-9 !py-1.5" placeholder="Search order ID, name, phone, TrxID, slug…">
            </form>
            <a href="{{ url('/') }}" target="_blank" class="hidden text-sm font-medium text-stone-500 hover:text-stone-900 sm:block">View site ↗</a>
            <div class="relative" x-data="{ o: false }" @click.outside="o = false">
                <button class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm font-medium hover:bg-stone-100" @click="o = !o">
                    <span class="grid h-7 w-7 place-items-center rounded-full bg-stone-900 text-xs font-bold text-white">{{ strtoupper(mb_substr($admin->name, 0, 1)) }}</span>
                    <span class="hidden sm:block">{{ $admin->name }}</span>
                </button>
                <div x-show="o" x-cloak class="absolute right-0 mt-1 w-48 rounded-lg bg-white py-1 shadow-lg ring-1 ring-stone-200">
                    <a href="{{ route('admin.account.edit') }}" class="block px-3 py-2 text-sm hover:bg-stone-50">My account</a>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="block w-full px-3 py-2 text-left text-sm hover:bg-stone-50">Log out</button></form>
                </div>
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))<div class="mb-5 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-emerald-200">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="mb-5 rounded-lg bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 ring-1 ring-rose-200">{{ session('error') }}</div>@endif
            @if ($errors->any() && ! ($hideErrorSummary ?? false))
                <div class="mb-5 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-200">
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

{{-- Confirm dialog --}}
<div x-data="confirmHost" x-show="open" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/40 p-4" @keydown.escape.window="cancel()" role="dialog" aria-modal="true">
    <div class="w-full max-w-sm rounded-xl bg-white p-5 shadow-xl" @click.outside="cancel()">
        <p class="text-base font-semibold">Please confirm</p>
        <p class="mt-1 text-sm text-stone-600" x-text="message"></p>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" class="a-btn a-btn-ghost" @click="cancel()">Cancel</button>
            <button type="button" class="a-btn" :class="danger ? 'a-btn-danger' : 'a-btn-primary'" x-ref="ok" @click="ok()">Confirm</button>
        </div>
    </div>
</div>
</body>
</html>
