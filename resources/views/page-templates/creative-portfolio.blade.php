@extends('page-templates._base')
@section('body')
@php $photo = $d->image('profile_photo'); $role = $d->text('role'); @endphp
<header class="px-5 pt-8 sm:px-10">
    <div class="flex items-center justify-between text-sm font-bold uppercase tracking-widest">
        <span>{{ $d->text('full_name') }}</span>
        @if ($d->has('email'))<a href="mailto:{{ $d->text('email') }}" class="no-underline">Say hi →</a>@endif
    </div>
    <h1 class="font-heading t-reveal mt-10 text-[12.5vw] [overflow-wrap:break-word] font-extrabold uppercase leading-[0.86] tracking-tight sm:text-[11vw]" data-split {{ $d->f('full_name') }}>{{ $d->text('full_name') }}</h1>
    <div class="mt-8 grid gap-8 md:grid-cols-[1fr_auto] md:items-end">
        @if ($d->has('intro'))<p class="t-reveal max-w-2xl text-2xl font-medium leading-snug sm:text-3xl" {{ $d->f('intro') }}>{{ $d->text('intro') }}</p>@endif
        @if ($photo)
            <div class="relative h-36 w-36 shrink-0 sm:h-44 sm:w-44">
                <img src="{{ $photo['url'] }}" {{ $d->f('profile_photo') }} alt="{{ $d->text('full_name') }}" class="h-full w-full rounded-full object-cover" width="176" height="176">
                <span class="bg-accent absolute -bottom-2 -left-3 rotate-[-8deg] rounded-full px-3 py-1 text-sm font-bold text-black">hello!</span>
            </div>
        @endif
    </div>
</header>

<div class="bg-accent mt-12 overflow-hidden py-4 text-black" aria-hidden="true">
    <div class="t-marquee font-heading gap-8 whitespace-nowrap text-3xl font-extrabold uppercase">
        @for ($i = 0; $i < 8; $i++)<span>{{ $role }} ✺</span>@endfor
    </div>
</div>

<main class="px-5 py-16 sm:px-10">
    @if ($d->gallery('works'))
        <section {{ $d->f('works') }}>
            <h2 class="font-heading text-sm font-bold uppercase tracking-widest">Selected work</h2>
            <div class="mt-6">@include('page-templates.partials.gallery', ['items' => $d->gallery('works'), 'style' => 'masonry'])</div>
        </section>
    @endif

    @if ($d->has('services'))
        <section class="mt-20" {{ $d->f('services') }}>
            <h2 class="font-heading text-sm font-bold uppercase tracking-widest">What I do</h2>
            <ol class="mt-6 border-t-2 border-current">
                @foreach ($d->items('services') as $i => $s)
                    <li class="t-reveal grid gap-2 border-b-2 border-current py-6 sm:grid-cols-[5rem_1fr_1.2fr] sm:items-baseline">
                        <span class="font-heading text-xl font-bold">{{ sprintf('%02d', $i + 1) }}</span>
                        <h3 class="font-heading text-3xl font-extrabold uppercase">{{ $s['title'] ?? '' }}</h3>
                        <p class="text-lg">{{ $s['text'] ?? '' }}</p>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    <section class="mt-20 grid gap-10 md:grid-cols-2">
        @if ($d->has('about'))<div class="t-reveal t-prose text-xl" {{ $d->f('about') }}>{{ $d->para('about') }}</div>@endif
        @if ($d->has('clients'))
            <div class="t-reveal" {{ $d->f('clients') }}>
                <h2 class="font-heading text-sm font-bold uppercase tracking-widest">Clients & friends</h2>
                <ul class="mt-4 flex flex-wrap gap-2">@foreach ($d->list('clients') as $c)<li class="rounded-full border-2 border-current px-4 py-1.5 font-semibold">{{ $c }}</li>@endforeach</ul>
            </div>
        @endif
    </section>

    <section class="mt-24 text-center">
        <p class="font-heading text-sm font-bold uppercase tracking-widest">Got a project?</p>
        @if ($d->has('email'))<a href="mailto:{{ $d->text('email') }}" class="font-heading mt-3 block break-all text-[9vw] font-extrabold leading-none no-underline hover:t-accent sm:text-6xl">{{ $d->text('email') }}</a>@endif
        @if ($d->has('phone'))<p class="mt-5 text-lg" {{ $d->f('phone') }}><a href="{{ $d->whatsapp($d->text('phone')) }}" target="_blank" rel="noopener">WhatsApp · {{ $d->text('phone') }}</a></p>@endif
        <div class="mt-8 flex justify-center">@include('page-templates.partials.socials')</div>
    </section>
</main>
@endsection
