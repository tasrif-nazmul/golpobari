@extends('page-templates._base')
@section('body')
@php
    $hero = $d->image('hero_photo');
    $since = $d->date('start_date');
    $rel = $d->text('relationship');
@endphp
@include('page-templates.partials.gate', [
    'style' => 'envelope', 'icon' => '💌', 'mode' => 'hearts', 'initial' => mb_substr($d->text('their_name'), 0, 1),
    'eyebrow' => $rel ? 'For my '.strtolower($rel) : 'For you',
    'title' => $d->text('their_name').', তোমার জন্য',
    'sub' => 'কিছু কথা অনেকদিন ধরে জমিয়ে রেখেছি…',
    'btn' => 'সিলমোহর ছুঁয়ে চিঠিটা খোলো',
])

<header class="relative overflow-hidden px-5 pb-20 pt-16 text-center sm:pt-24">
    @include('page-templates.partials.particles', ['chars' => ['❀', '♥', '✿', '❤'], 'count' => 16, 'class' => 't-accent'])
    <div class="pointer-events-none absolute -left-24 -top-24 h-80 w-80 rounded-full opacity-40 blur-3xl" style="background: var(--t-accent)"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-16 h-72 w-72 rounded-full opacity-25 blur-3xl" style="background: var(--t-primary)"></div>

    <p class="font-accent t-reveal relative text-xl italic text-soft" {{ $d->f('relationship') }}>{{ $rel ? 'For my '.strtolower($rel) : 'For you' }}</p>
    <h1 class="font-heading relative mt-3 text-[3.4rem] leading-[1.05] t-primary sm:text-8xl" data-split>
        <span {{ $d->f('your_name') }}>{{ $d->text('your_name') }}</span> <span class="t-accent">&amp;</span> <span {{ $d->f('their_name') }}>{{ $d->text('their_name') }}</span>
    </h1>

    @if ($hero)
        <div class="t-reveal relative mx-auto mt-14 w-64 sm:w-80" {{ $d->f('hero_photo') }}>
            <div class="t-blob absolute -inset-5 opacity-60" style="background: var(--t-accent)"></div>
            <svg class="t-spin absolute -inset-10 h-[calc(100%+5rem)] w-[calc(100%+5rem)]" viewBox="0 0 200 200" aria-hidden="true">
                <defs><path id="ring" d="M100,100 m-88,0 a88,88 0 1,1 176,0 a88,88 0 1,1 -176,0"/></defs>
                <text font-size="10" letter-spacing="3" fill="currentColor" class="t-primary"><textPath href="#ring">♥ {{ $d->text('your_name') }} ♥ {{ $d->text('their_name') }} ♥ forever ♥ always ♥ forever ♥</textPath></text>
            </svg>
            <div data-tilt class="relative transition-transform duration-300">
                <img src="{{ $hero['url'] }}" alt="{{ $d->text('your_name') }} ও {{ $d->text('their_name') }}" class="t-blob aspect-square w-full object-cover shadow-2xl" width="320" height="320">
            </div>
            <span class="absolute -right-2 bottom-4 grid h-14 w-14 place-items-center rounded-full bg-white text-2xl shadow-lg t-bob" aria-hidden="true">💕</span>
        </div>
    @endif

    @if ($d->has('headline'))<p class="font-heading t-reveal mx-auto mt-14 max-w-xl text-4xl leading-snug t-primary sm:text-5xl" {{ $d->f('headline') }}>{{ $d->text('headline') }}</p>@endif

    @if ($since)
        <div class="t-reveal mx-auto mt-10 inline-flex items-center gap-4 rounded-full bg-white/80 py-3 pl-3 pr-7 shadow-[0_20px_40px_-20px_rgba(122,31,61,.45)] backdrop-blur" {{ $d->f('start_date') }}>
            <span class="grid h-14 w-14 place-items-center rounded-full bg-primary text-2xl text-white" style="animation: t-bob 1.2s ease-in-out infinite">♥</span>
            <span class="text-left">
                <span class="block text-4xl font-bold leading-none t-primary" data-since="{{ $since->toDateString() }}">{{ \App\Support\Bn::digits(max(0, (int) $since->diffInDays(now()))) }}</span>
                <span class="text-sm font-semibold text-soft">দিন ধরে একসাথে</span>
            </span>
        </div>
    @endif
</header>

<main class="px-5 pb-16">
    <section class="t-reveal t-grain relative mx-auto max-w-2xl rounded-[2rem] bg-white/70 px-6 py-10 shadow-[0_30px_60px_-40px_rgba(122,31,61,.5)] sm:px-12" {{ $d->f('story') }}>
        <p class="t-eyebrow t-accent text-center">আমাদের গল্প</p>
        @include('page-templates.partials.shapla', ['class' => 'mx-auto mt-3 h-8 t-accent'])
        <div class="t-prose mt-6 text-lg leading-loose first-letter:float-left first-letter:mr-3 first-letter:font-heading first-letter:text-7xl first-letter:leading-[.8] first-letter:t-primary">{{ $d->para('story') }}</div>
    </section>

    @if ($d->has('favorite_memories'))
        <section class="mx-auto mt-24 max-w-5xl" {{ $d->f('favorite_memories') }}>
            <h2 class="t-section-title text-center text-5xl t-primary sm:text-6xl">প্রিয় মুহূর্তগুলো</h2>
            <div class="no-scrollbar -mx-5 mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-5 pb-6 sm:mx-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:px-0 lg:grid-cols-3">
                @foreach ($d->items('favorite_memories') as $i => $m)
                    <article class="t-reveal relative w-[78%] shrink-0 snap-center overflow-hidden rounded-[1.6rem] bg-white p-6 shadow-[0_18px_40px_-22px_rgba(122,31,61,.5)] sm:w-auto" style="--i: {{ $i }}">
                        <span class="font-heading absolute -right-2 -top-5 text-8xl opacity-10 t-primary">{{ \App\Support\Bn::digits(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        <p class="t-accent text-3xl">♥</p>
                        <h3 class="mt-2 text-xl font-bold t-primary">{{ $m['title'] ?? '' }}</h3>
                        <p class="mt-1 text-soft">{{ $m['text'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-20 max-w-5xl" {{ $d->f('gallery') }}>
            <p class="font-accent text-center text-2xl italic text-soft">ছবিতে আমরা</p>
            <div class="mt-8">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'polaroid'])</div>
        </section>
    @endif

    @if ($d->has('quotes'))
        <section class="mx-auto mt-24 max-w-2xl space-y-12 text-center" {{ $d->f('quotes') }}>
            @foreach ($d->items('quotes') as $i => $q)
                <blockquote class="font-accent t-reveal text-3xl italic leading-snug t-primary sm:text-4xl" style="--i: {{ $i }}"><span class="t-accent">“</span>{{ $q['text'] ?? '' }}<span class="t-accent">”</span></blockquote>
            @endforeach
        </section>
    @endif

    <section class="t-reveal relative mx-auto mt-24 max-w-2xl overflow-hidden rounded-[2.4rem] bg-primary px-7 pb-12 pt-14 text-center text-white sm:px-12" {{ $d->f('special_message') }}>
        @include('page-templates.partials.particles', ['chars' => ['♥'], 'count' => 10, 'rise' => true, 'class' => 'opacity-40'])
        <p class="font-heading relative text-5xl t-accent">{{ $d->text('their_name') }},</p>
        <div class="t-prose relative mt-6 text-lg leading-loose text-white/90">{{ $d->para('special_message') }}</div>
        <p class="font-heading relative mt-8 text-4xl">— {{ $d->text('your_name') }}</p>
        <button type="button" class="t-btn t-btn--accent relative mt-10" data-burst="❤">❤ ভালোবাসা পাঠাও</button>
    </section>
</main>
@endsection
