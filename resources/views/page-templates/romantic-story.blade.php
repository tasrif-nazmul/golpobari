@extends('page-templates._base')
@section('body')
@php
    $hero = $d->image('hero_photo');
    $since = $d->date('start_date');
@endphp
<header class="relative overflow-hidden px-5 pb-16 pt-14 text-center sm:pt-20">
    <div class="t-hearts pointer-events-none absolute inset-0" aria-hidden="true">
        @foreach ([8, 22, 37, 55, 68, 83, 93] as $i => $left)<span style="left: {{ $left }}%; animation-delay: {{ $i * 1.3 }}s; color: var(--t-accent)">♥</span>@endforeach
    </div>
    <p class="font-accent t-reveal text-xl italic text-soft">{{ $d->text('relationship') ? 'For my '.strtolower($d->text('relationship')) : 'For you' }}</p>
    <h1 class="font-heading t-reveal mt-2 text-[3.6rem] leading-none t-primary sm:text-8xl">{{ $d->text('your_name') }} <span class="t-accent">&amp;</span> {{ $d->text('their_name') }}</h1>
    @if ($hero)
        <div class="t-reveal relative mx-auto mt-10 w-64 sm:w-80">
            <svg class="t-spin absolute -inset-6 h-[calc(100%+3rem)] w-[calc(100%+3rem)]" viewBox="0 0 200 200" aria-hidden="true">
                <defs><path id="ring" d="M100,100 m-88,0 a88,88 0 1,1 176,0 a88,88 0 1,1 -176,0"/></defs>
                <text font-size="11" letter-spacing="3" fill="currentColor" class="t-primary"><textPath href="#ring">♥ {{ $d->text('your_name') }} ♥ {{ $d->text('their_name') }} ♥ forever ♥ always ♥ </textPath></text>
            </svg>
            <img src="{{ $hero['url'] }}" alt="{{ $d->text('your_name') }} ও {{ $d->text('their_name') }}" class="aspect-square w-full rounded-full object-cover shadow-2xl" width="320" height="320">
        </div>
    @endif
    @if ($d->has('headline'))<p class="font-heading t-reveal mx-auto mt-12 max-w-xl text-4xl t-primary sm:text-5xl">{{ $d->text('headline') }}</p>@endif
    @if ($since)
        <div class="t-reveal mt-8">
            <p class="font-accent text-6xl font-bold t-primary sm:text-7xl" data-since="{{ $since->toDateString() }}">{{ \App\Support\Bn::digits(max(0, (int) $since->diffInDays(now()))) }}</p>
            <p class="text-sm font-semibold uppercase tracking-[0.25em] text-soft">দিন একসাথে</p>
        </div>
    @endif
</header>

<main class="px-5 pb-16">
    <section class="t-reveal mx-auto max-w-2xl">
        <div class="t-prose text-lg leading-loose first-letter:float-left first-letter:mr-2 first-letter:font-heading first-letter:text-6xl first-letter:leading-none first-letter:t-primary">{{ $d->para('story') }}</div>
    </section>

    @if ($d->has('favorite_memories'))
        <section class="mx-auto mt-20 max-w-4xl">
            <h2 class="font-heading text-center text-5xl t-primary">প্রিয় মুহূর্তগুলো</h2>
            <div class="mt-10 grid gap-5 sm:grid-cols-2">
                @foreach ($d->items('favorite_memories') as $i => $m)
                    <article class="t-reveal rounded-3xl bg-white/80 p-6 shadow-[0_15px_40px_-20px_rgba(122,31,61,.4)]" style="--i: {{ $i }}">
                        <p class="t-accent text-2xl">♥</p>
                        <h3 class="mt-2 text-xl font-bold t-primary">{{ $m['title'] ?? '' }}</h3>
                        <p class="mt-1 text-soft">{{ $m['text'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-20 max-w-5xl">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'polaroid'])</section>
    @endif

    @if ($d->has('quotes'))
        <section class="mx-auto mt-20 max-w-2xl space-y-10 text-center">
            @foreach ($d->items('quotes') as $q)
                <blockquote class="font-accent t-reveal text-3xl italic leading-snug t-primary sm:text-4xl">“{{ $q['text'] ?? '' }}”</blockquote>
            @endforeach
        </section>
    @endif

    <section class="t-reveal mx-auto mt-20 max-w-2xl rounded-[2rem] bg-primary px-7 py-12 text-center text-white sm:px-12">
        <p class="font-heading text-4xl t-accent">{{ $d->text('their_name') }},</p>
        <div class="t-prose mt-5 text-lg leading-loose text-white/90">{{ $d->para('special_message') }}</div>
        <p class="font-heading mt-8 text-3xl">— {{ $d->text('your_name') }}</p>
    </section>
</main>
@endsection
