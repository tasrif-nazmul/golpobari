@extends('page-templates._base')
@section('body')
@php $cover = $d->image('cover_photo'); $photos = $d->gallery('gallery'); $items = $d->items('timeline'); @endphp
<header class="t-grain relative overflow-hidden px-5 pb-16 pt-16 text-center sm:pt-24">
    <p class="font-accent t-reveal text-3xl t-accent" {{ $d->f('your_name their_name') }}>{{ $d->text('your_name') }} ♡ {{ $d->text('their_name') }}</p>
    <h1 class="font-heading mt-3 text-5xl leading-tight t-primary sm:text-7xl" data-split {{ $d->f('title') }}>{{ $d->text('title', 'আমাদের গল্প') }}</h1>
    @if ($cover)
        <figure class="t-reveal relative mx-auto mt-12 w-64 rotate-[-3deg] bg-white p-3 pb-12 shadow-2xl sm:w-80" {{ $d->f('cover_photo') }}>
            <span class="t-tape -top-3 left-1/2 -translate-x-1/2 rotate-2" aria-hidden="true"></span>
            <img src="{{ $cover['url'] }}" alt="" class="aspect-[4/5] w-full object-cover" width="320" height="400">
            <figcaption class="font-accent absolute bottom-2 left-0 right-0 text-2xl t-primary">যেখান থেকে শুরু…</figcaption>
        </figure>
    @endif
    @if ($d->has('intro'))<div class="t-reveal t-prose mx-auto mt-12 max-w-xl text-lg text-soft" {{ $d->f('intro') }}>{{ $d->para('intro') }}</div>@endif
    <p class="mt-10 animate-bounce text-2xl t-accent" aria-hidden="true">↓</p>
</header>

<main class="relative px-5 pb-20" data-draw-host {{ $d->f('timeline') }}>
    {{-- The thread that draws itself as you scroll --}}
    <svg class="pointer-events-none absolute left-7 top-0 h-full w-6 sm:left-1/2 sm:-translate-x-1/2" viewBox="0 0 24 1000" preserveAspectRatio="none" aria-hidden="true">
        <path d="M12 0 C 22 120, 2 240, 12 360 S 22 600, 12 720 S 2 900, 12 1000" fill="none" stroke="var(--t-accent)" stroke-width="2.5" stroke-dasharray="1" data-draw vector-effect="non-scaling-stroke"/>
    </svg>
    <ol class="relative mx-auto max-w-4xl space-y-14 sm:space-y-20">
        @foreach ($items as $i => $t)
            @php $img = $photos[$i] ?? null; $right = $i % 2 === 1; @endphp
            <li class="t-reveal relative pl-12 sm:grid sm:grid-cols-2 sm:gap-14 sm:pl-0" style="--i: 0">
                <span class="absolute left-[1.05rem] top-3 grid h-6 w-6 place-items-center rounded-full bg-accent text-[10px] text-white ring-4 ring-[color:var(--t-bg)] sm:left-1/2 sm:-translate-x-1/2">♥</span>
                <div class="{{ $right ? 'sm:order-2 sm:text-left' : 'sm:text-right' }}">
                    <p class="font-accent text-2xl t-accent">{{ $t['date'] ?? '' }}</p>
                    <h2 class="font-heading mt-1 text-3xl t-primary sm:text-4xl">{{ $t['title'] ?? '' }}</h2>
                    <p class="mt-2 text-lg text-soft">{{ $t['text'] ?? '' }}</p>
                </div>
                @if ($img)
                    <figure class="relative mt-5 sm:mt-0 {{ $right ? 'sm:order-1' : '' }}" {{ $d->f('gallery') }}>
                        <div class="t-swing mx-auto w-56 bg-white p-2.5 pb-8 shadow-xl {{ $right ? 'sm:ml-auto sm:mr-0' : 'sm:ml-0' }}" style="--i: {{ $i }}">
                            <span class="t-pin" aria-hidden="true"></span>
                            <img src="{{ $img['thumb'] ?? $img['url'] }}" alt="{{ $img['caption'] ?? '' }}" class="aspect-square w-full object-cover" loading="lazy">
                            @if (! empty($img['caption']))<figcaption class="font-accent mt-1 text-center text-lg text-[#333]">{{ $img['caption'] }}</figcaption>@endif
                        </div>
                    </figure>
                @endif
            </li>
        @endforeach
    </ol>

    @if (count($photos) > count($items))
        <section class="mx-auto mt-24 max-w-5xl" {{ $d->f('gallery') }}>
            <p class="font-accent text-center text-3xl t-accent">আরও কিছু মুহূর্ত</p>
            <div class="mt-8">@include('page-templates.partials.gallery', ['items' => array_slice($photos, count($items)), 'style' => 'polaroid'])</div>
        </section>
    @endif

    @if ($d->has('closing_message'))
        <section class="t-reveal relative mx-auto mt-24 max-w-xl rounded-[2rem] bg-primary px-8 py-12 text-center text-white" {{ $d->f('closing_message') }}>
            <p class="text-4xl" aria-hidden="true">💛</p>
            <div class="font-accent t-prose mt-4 text-3xl leading-snug">{{ $d->para('closing_message') }}</div>
            <p class="mt-6 text-white/70">— {{ $d->text('your_name') }}</p>
        </section>
    @endif
</main>
@endsection
