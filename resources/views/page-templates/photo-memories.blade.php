@extends('page-templates._base')
@section('body')
@php $cover = $d->image('cover_photo'); @endphp
<header class="relative">
    @if ($cover)
        <div class="relative h-[78dvh] overflow-hidden">
            <img src="{{ $cover['url'] }}" alt="" class="h-full w-full object-cover" width="1600" height="1100">
            <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 35%, var(--t-bg))"></div>
        </div>
    @endif
    <div class="relative mx-auto max-w-3xl px-5 text-center {{ $cover ? '-mt-40' : 'pt-20' }}">
        @if ($d->has('date_text'))<p class="font-accent t-reveal text-2xl t-accent">{{ $d->text('date_text') }}</p>@endif
        <h1 class="font-heading t-reveal mt-2 text-5xl leading-tight sm:text-7xl">{{ $d->text('title') }}</h1>
        @if ($d->has('subtitle'))<p class="t-reveal mt-4 text-lg italic opacity-70">{{ $d->text('subtitle') }}</p>@endif
    </div>
</header>

<main class="px-5 pb-16 pt-14">
    @if ($d->has('intro'))<div class="t-prose t-reveal mx-auto max-w-2xl text-center text-lg opacity-85">{{ $d->para('intro') }}</div>@endif

    <section class="mx-auto mt-16 max-w-6xl">
        @include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'prints'])
    </section>

    @if ($d->has('people'))
        <section class="t-reveal mx-auto mt-20 max-w-2xl text-center">
            <p class="text-sm uppercase tracking-[0.3em] opacity-60">In this story</p>
            <p class="font-accent mt-3 text-3xl t-accent">{{ implode(' · ', $d->list('people')) }}</p>
        </section>
    @endif

    @if ($d->has('closing_note'))
        <p class="font-heading t-reveal mx-auto mt-16 max-w-2xl text-center text-3xl italic leading-snug">{{ $d->text('closing_note') }}</p>
    @endif
</main>
@endsection
