@extends('page-templates._base')
@section('body')
@php
    $hero = $d->image('hero_photo');
    $date = $d->date('anniversary_date');
    $roman = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII', 'XIII', 'XIV', 'XV'];
@endphp
<header class="relative grid min-h-[92dvh] place-items-center overflow-hidden px-5 py-20 text-center">
    @if ($hero)<img src="{{ $hero['url'] }}" alt="" class="absolute inset-0 h-full w-full object-cover" width="1600" height="1000">@endif
    <div class="absolute inset-0" style="background: linear-gradient(180deg, color-mix(in oklab, var(--t-bg) 55%, transparent), var(--t-bg) 92%)"></div>
    <div class="relative">
        <p class="t-reveal text-sm uppercase tracking-[0.4em] t-accent">Our journey</p>
        <h1 class="font-heading t-reveal mt-5 text-6xl font-medium leading-[0.95] sm:text-8xl">{{ $d->text('partner_one') }}<br><span class="font-heading text-5xl italic t-accent sm:text-6xl">&amp;</span><br>{{ $d->text('partner_two') }}</h1>
        @if ($date)
            <p class="t-reveal mt-8 text-lg opacity-80">{{ \App\Support\Bn::date($date) }} থেকে একসাথে</p>
            <p class="font-heading t-reveal mt-2 text-3xl t-accent" data-since="{{ $date->toDateString() }}" data-unit="ymd">&nbsp;</p>
        @endif
    </div>
</header>

<main class="px-5 pb-16">
    @if ($d->has('intro'))<p class="font-heading t-reveal mx-auto max-w-2xl text-center text-3xl italic leading-snug opacity-90 sm:text-4xl">{{ $d->text('intro') }}</p>@endif

    @if ($d->has('milestones'))
        <section class="mx-auto mt-20 max-w-3xl space-y-16">
            @foreach ($d->items('milestones') as $i => $m)
                <article class="t-reveal grid gap-4 sm:grid-cols-[6rem_1fr]">
                    <p class="font-heading text-6xl t-accent">{{ $roman[$i] ?? $i + 1 }}</p>
                    <div class="border-t pt-4" style="border-color: color-mix(in oklab, var(--t-accent) 40%, transparent)">
                        <p class="text-sm uppercase tracking-[0.25em] t-accent">{{ $m['date'] ?? '' }}</p>
                        <h2 class="font-heading mt-1 text-3xl">{{ $m['title'] ?? '' }}</h2>
                        <p class="mt-2 text-lg opacity-80">{!! \App\Support\TemplateData::lines($m['text'] ?? '') !!}</p>
                    </div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-24 max-w-5xl [&_img]:rounded-none [&_img]:ring-1 [&_img]:ring-[var(--t-accent)]/40">
            @include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'grid'])
        </section>
    @endif

    <section class="t-reveal mx-auto mt-24 max-w-2xl border px-6 py-14 text-center sm:px-12" style="border-color: var(--t-accent)">
        <p class="text-3xl t-accent" aria-hidden="true">❦</p>
        <div class="font-heading t-prose mt-4 text-2xl leading-relaxed">{{ $d->para('message') }}</div>
    </section>
</main>
@endsection
