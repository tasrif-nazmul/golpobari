@extends('page-templates._base')
@section('body')
@php
    $logo = $d->image('logo'); $cover = $d->image('cover_photo');
    $phone = $d->text('phone'); $wa = $d->whatsapp($d->text('whatsapp') ?: $phone);
@endphp
<header class="sticky top-0 z-30 border-b border-black/5 bg-white/90 backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-5 py-3">
        <div class="flex items-center gap-3">
            @if ($logo)<img src="{{ $logo['url'] }}" alt="" class="h-10 w-10 rounded-full object-cover" width="40" height="40">@endif
            <span class="font-heading text-lg font-extrabold">{{ $d->text('business_name') }}</span>
        </div>
        @if ($phone)<a href="tel:{{ $d->tel($phone) }}" class="t-btn t-btn--primary !min-h-[2.6rem] !px-4 text-sm">কল করুন</a>@endif
    </div>
</header>

<section class="relative">
    @if ($cover)<img src="{{ $cover['url'] }}" alt="{{ $d->text('business_name') }}" class="h-[46vh] w-full object-cover sm:h-[60vh]" width="1600" height="900">@endif
    <div class="mx-auto max-w-6xl px-5">
        <div class="t-reveal relative {{ $cover ? '-mt-24' : 'mt-14' }} rounded-3xl bg-white p-7 shadow-xl sm:p-10 md:max-w-2xl">
            <h1 class="font-heading text-4xl font-extrabold leading-tight sm:text-5xl t-primary">{{ $d->text('business_name') }}</h1>
            @if ($d->has('tagline'))<p class="mt-3 text-lg text-soft">{{ $d->text('tagline') }}</p>@endif
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="t-btn t-btn--primary">WhatsApp</a>@endif
                @if ($d->link($d->text('map_link')))<a href="{{ $d->link($d->text('map_link')) }}" target="_blank" rel="noopener nofollow" class="t-btn t-btn--ghost t-primary">ম্যাপে দেখুন</a>@endif
            </div>
        </div>
    </div>
</section>

<main class="mx-auto max-w-6xl px-5 pb-28 pt-16 sm:pb-16">
    <section class="t-reveal grid gap-6 md:grid-cols-[1fr_1.4fr]">
        <h2 class="font-heading text-3xl font-extrabold">আমাদের কথা</h2>
        <div class="t-prose text-lg text-soft">{{ $d->para('about') }}</div>
    </section>

    @if ($d->has('services'))
        <section class="mt-16">
            <h2 class="font-heading text-3xl font-extrabold">যা পাবেন</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($d->items('services') as $i => $s)
                    <article class="t-reveal flex flex-col rounded-3xl p-6 mix-10" style="--i: {{ $i }}">
                        <h3 class="text-xl font-bold">{{ $s['name'] ?? '' }}</h3>
                        <p class="mt-1 flex-1 text-soft">{{ $s['description'] ?? '' }}</p>
                        @if (! empty($s['price']))<p class="mt-4 font-heading text-2xl font-extrabold t-primary">{{ $s['price'] }}</p>@endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mt-16">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'grid'])</section>
    @endif

    <section class="t-reveal mt-16 grid gap-4 rounded-3xl bg-primary p-7 text-white sm:grid-cols-2 sm:p-10">
        <div>
            <h2 class="font-heading text-2xl font-extrabold">ঠিকানা ও সময়</h2>
            @if ($d->has('address'))<p class="mt-3 text-white/85">{!! \App\Support\TemplateData::lines($d->text('address')) !!}</p>@endif
            @if ($d->has('hours'))<p class="mt-3 text-white/85">{!! \App\Support\TemplateData::lines($d->text('hours')) !!}</p>@endif
        </div>
        <div class="space-y-2">
            @if ($phone)<p><a href="tel:{{ $d->tel($phone) }}" class="text-xl font-bold">{{ $phone }}</a></p>@endif
            @if ($d->has('email'))<p><a href="mailto:{{ $d->text('email') }}" class="break-all">{{ $d->text('email') }}</a></p>@endif
            <div class="pt-3">@include('page-templates.partials.socials')</div>
        </div>
    </section>
</main>

@if ($phone || $wa)
<div class="fixed inset-x-0 bottom-0 z-30 flex gap-2 border-t border-black/5 bg-white/95 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:hidden">
    @if ($phone)<a href="tel:{{ $d->tel($phone) }}" class="t-btn t-btn--ghost t-primary flex-1">কল</a>@endif
    @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="t-btn t-btn--primary flex-1">WhatsApp</a>@endif
</div>
@endif
@endsection
