@extends('page-templates._base')
@section('body')
@php $cover = $d->image('cover_photo'); $people = $d->list('people'); @endphp
<header class="relative min-h-[92svh] overflow-hidden" {{ $d->f('cover_photo') }}>
    @if ($cover)<img src="{{ $cover['url'] }}" alt="" class="absolute inset-0 h-full w-full scale-105 object-cover" style="animation: t-kenburns 18s ease-in-out infinite alternate" width="1200" height="1500">@endif
    <div class="absolute inset-0 bg-gradient-to-b from-black/30 via-black/20 to-[var(--t-bg)]"></div>
    <div class="t-leak"></div>
    <div class="relative flex min-h-[92svh] flex-col justify-end px-6 pb-16 pt-24 sm:px-12">
        <p class="t-eyebrow t-accent" {{ $d->f('date_text') }}>{{ $d->text('date_text', 'স্মৃতির অ্যালবাম') }}</p>
        <h1 class="font-heading mt-3 max-w-3xl text-5xl leading-[1.05] sm:text-8xl" data-split {{ $d->f('title') }}>{{ $d->text('title') }}</h1>
        @if ($d->has('subtitle'))<p class="font-accent mt-4 max-w-xl text-2xl opacity-90 sm:text-3xl" {{ $d->f('subtitle') }}>{{ $d->text('subtitle') }}</p>@endif
    </div>
</header>

<div class="t-film t-marquee-wrap" aria-hidden="true">
    <div class="t-marquee gap-3 px-2">
        @foreach (array_merge($d->gallery('gallery'), $d->gallery('gallery')) as $img)
            <img src="{{ $img['thumb'] ?? $img['url'] }}" alt="" class="h-24 w-32 shrink-0 object-cover opacity-90" loading="lazy">
        @endforeach
    </div>
</div>

<main class="px-5 pb-20 pt-16">
    @if ($d->has('intro'))<div class="t-reveal t-prose mx-auto max-w-2xl text-xl leading-loose opacity-90" {{ $d->f('intro') }}>{{ $d->para('intro') }}</div>@endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-20 max-w-6xl" {{ $d->f('gallery') }}>@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'prints'])</section>
    @endif

    @if ($people)
        <section class="relative mx-auto mt-24 h-72 max-w-md overflow-hidden text-center" style="mask-image: linear-gradient(transparent, #000 20%, #000 80%, transparent)" {{ $d->f('people') }}>
            <div class="t-credits space-y-6">
                <p class="t-eyebrow t-accent">অভিনয়ে</p>
                @foreach ($people as $p)<p class="font-heading text-3xl">{{ $p }}</p>@endforeach
                <p class="t-eyebrow t-accent pt-6">পরিচালনায়</p>
                <p class="font-heading text-3xl">আমরা সবাই</p>
            </div>
        </section>
    @endif

    <section class="t-reveal mx-auto mt-24 max-w-xl text-center" {{ $d->f('closing_note') }}>
        @if ($d->has('closing_note'))<div class="font-accent t-prose text-3xl leading-snug t-accent">{{ $d->para('closing_note') }}</div>@endif
        <p class="font-heading mt-12 text-6xl">সমাপ্ত</p>
        <p class="mt-2 text-sm opacity-60">…নাকি শুরু?</p>
    </section>
</main>
@endsection
