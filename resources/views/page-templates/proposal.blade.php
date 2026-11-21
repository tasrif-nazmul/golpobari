@extends('page-templates._base')
@section('body')
@php $hero = $d->image('hero_photo'); $lines = $d->items('lines'); $wa = $d->whatsapp($d->text('whatsapp')); @endphp
@include('page-templates.partials.gate', [
    'style' => 'envelope', 'mode' => 'hearts', 'eyebrow' => 'শুধু তোমার জন্য', 'initial' => '♥',
    'paper' => '#f6dfe3', 'ribbon' => '#b8324f', 'seal' => '#8e1537', 'bg' => '#3a1630',
    'title' => $d->text('their_name').', তোমার জন্য একটা প্রশ্ন আছে',
    'sub' => 'একটু সময় নিয়ে পড়ো…', 'btn' => 'সিলমোহর ছুঁয়ে খোলো',
])
<main class="relative min-h-dvh overflow-hidden px-5 pb-24 pt-16 text-center">
    <div class="pointer-events-none fixed inset-0" aria-hidden="true">
        @for ($i = 0; $i < 40; $i++)<span class="t-star" style="left: {{ ($i * 53) % 100 }}%; top: {{ ($i * 29 + 7) % 100 }}%; animation-delay: -{{ $i % 7 }}s; opacity: .{{ 3 + $i % 6 }}"></span>@endfor
    </div>
    @include('page-templates.partials.particles', ['chars' => ['♥', '❤', '💕'], 'count' => 12, 'rise' => true, 'class' => 't-accent opacity-50'])

    <section class="relative mx-auto max-w-xl">
        <p class="font-accent t-eyebrow t-accent" {{ $d->f('your_name') }}>{{ $d->text('your_name') }}-এর মনের কথা</p>
        <h1 class="font-heading mt-3 text-7xl leading-none sm:text-8xl" data-split {{ $d->f('their_name') }}>{{ $d->text('their_name') }}</h1>

        @if ($hero)
            <div class="t-reveal relative mx-auto mt-12 w-56 sm:w-64" {{ $d->f('hero_photo') }}>
                <div class="absolute -inset-3 rounded-full opacity-70 blur-xl" style="background: var(--t-accent)"></div>
                <img src="{{ $hero['url'] }}" alt="" class="relative aspect-square w-full rounded-full border-4 object-cover" style="border-color: var(--t-accent)" width="256" height="256">
            </div>
        @endif

        <div class="t-seq mt-14 space-y-6" data-seq="#ask" {{ $d->f('lines') }}>
            @foreach ($lines as $l)
                <p data-seq-item class="font-accent rounded-3xl bg-white/5 px-6 py-6 text-2xl leading-relaxed ring-1 ring-white/10 backdrop-blur sm:text-3xl">{{ $l['text'] ?? '' }}</p>
            @endforeach
            <button type="button" class="t-btn t-btn--accent mt-4" data-seq-next data-last-label="এবার আসল কথা 💍">পরের কথা →</button>
        </div>
    </section>

    @include('page-templates.partials.surprises')
    <section id="ask" class="relative mx-auto mt-20 max-w-xl" {{ $d->f('question') }}>
        <div class="t-ring"></div>
        <h2 class="font-accent mt-10 text-4xl font-bold leading-tight sm:text-5xl">{{ $d->text('question', 'তুমি কি আমার হবে?') }}</h2>
        <div class="t-propose mt-10" data-proposal>
            <div class="flex items-center justify-center gap-4">
                <button type="button" class="t-btn t-btn--accent z-10 min-w-[8rem] text-xl" data-yes="#yes">হ্যাঁ 💖</button>
                <button type="button" class="t-btn t-btn--ghost min-w-[8rem] text-lg" data-no>না</button>
            </div>
        </div>
    </section>

    <section id="yes" hidden class="relative mx-auto mt-6 max-w-2xl" {{ $d->f('yes_message') }}>
        <p class="font-heading text-6xl t-accent">ইয়েস!</p>
        <div class="t-prose mt-6 text-xl leading-loose">{{ $d->para('yes_message') }}</div>
        @if ($d->gallery('gallery'))<div class="mt-12" {{ $d->f('gallery') }}>@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'polaroid'])</div>@endif
        @if ($wa)
            <a href="{{ $wa }}?text={{ rawurlencode('হ্যাঁ! 💍 আমি রাজি।') }}" target="_blank" rel="noopener" class="t-btn t-btn--accent mt-12 text-lg" {{ $d->f('whatsapp') }}>💬 {{ $d->text('your_name') }}-কে জানিয়ে দাও</a>
        @endif
    </section>
</main>
@endsection
