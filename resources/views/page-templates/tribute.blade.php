@extends('page-templates._base')
@section('body')
@php $portrait = $d->image('portrait'); @endphp
<header class="t-grain relative overflow-hidden px-5 pb-16 pt-20 text-center">
    <div class="t-candle-big t-reveal" aria-hidden="true"><i></i></div>
    <p class="font-accent t-reveal mt-12 text-xl text-soft" {{ $d->f('relation') }}>{{ $d->text('relation', 'চিরস্মরণীয়') }}</p>
    <h1 class="font-heading mt-2 text-5xl leading-tight t-primary sm:text-7xl" data-split {{ $d->f('full_name') }}>{{ $d->text('full_name') }}</h1>
    @if ($d->has('born') || $d->has('passed'))
        <p class="t-reveal mt-4 inline-flex items-center gap-3 text-lg tracking-wide text-soft" {{ $d->f('born passed') }}>
            <span>{{ $d->text('born') }}</span><span class="h-px w-10 bg-current opacity-40"></span><span>{{ $d->text('passed') }}</span>
        </p>
    @endif
    @if ($portrait)
        <div class="t-reveal relative mx-auto mt-12 w-60 sm:w-72" {{ $d->f('portrait') }}>
            <div class="absolute -inset-4 rounded-[50%] border opacity-50" style="border-color: var(--t-accent)"></div>
            <img src="{{ $portrait['url'] }}" alt="{{ $d->text('full_name') }}" class="aspect-[4/5] w-full rounded-[50%] object-cover shadow-[0_30px_60px_-30px_rgba(0,0,0,.5)]" style="filter: sepia(.25)" width="288" height="360">
        </div>
    @endif
</header>

<main class="px-5 pb-20">
    <section class="t-reveal mx-auto max-w-2xl" {{ $d->f('life_story') }}>
        <div class="t-divider"><span>❦</span></div>
        <div class="t-prose mt-8 text-lg leading-loose sm:text-xl">{{ $d->para('life_story') }}</div>
    </section>

    @if ($d->has('lessons'))
        <section class="mx-auto mt-20 max-w-2xl" {{ $d->f('lessons') }}>
            <h2 class="font-heading text-center text-4xl t-primary">যা শিখিয়ে গেছেন</h2>
            <ul class="mt-8 space-y-4">
                @foreach ($d->items('lessons') as $i => $l)
                    <li class="t-reveal flex gap-4 rounded-2xl bg-white/60 px-5 py-4 text-lg" style="--i: {{ $i }}"><span class="t-accent" aria-hidden="true">🌿</span><span>{{ $l['text'] ?? '' }}</span></li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="t-tribute-gallery mx-auto mt-20 max-w-5xl" {{ $d->f('gallery') }}>
            @include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'grid'])
        </section>
    @endif

    @include('page-templates.partials.surprises')
    <section class="t-reveal mx-auto mt-24 max-w-xl rounded-[2rem] bg-primary px-8 py-12 text-center text-white" {{ $d->f('prayer from_family') }}>
        @if ($d->has('prayer'))<div class="font-heading t-prose text-2xl leading-relaxed sm:text-3xl">{{ $d->para('prayer') }}</div>@endif
        @if ($d->has('from_family'))<p class="mt-6 text-white/75">— {{ $d->text('from_family') }}</p>@endif
        <div id="lamps" class="mt-6 min-h-[2.8rem]" aria-live="polite"></div>
        <button type="button" class="t-btn t-btn--accent mt-4" data-lamp="#lamps">🪔 <span>একটি প্রদীপ জ্বালান</span></button>
    </section>
</main>
@endsection
