@extends('page-templates._base')
@section('body')
@php $cover = $d->image('cover_photo'); $photos = $d->gallery('gallery'); @endphp
<header class="px-5 pt-14 text-center sm:pt-20">
    <p class="font-accent t-reveal text-3xl t-accent">{{ $d->text('your_name') }} + {{ $d->text('their_name') }}</p>
    <h1 class="font-heading t-reveal mx-auto mt-3 max-w-3xl text-5xl leading-tight t-primary sm:text-7xl">{{ $d->text('title', 'Our story') }}</h1>
    @if ($d->has('intro'))<p class="t-reveal mx-auto mt-6 max-w-xl text-lg text-soft">{{ $d->text('intro') }}</p>@endif
    @if ($cover)<img src="{{ $cover['url'] }}" alt="" class="t-reveal mx-auto mt-12 aspect-[16/10] w-full max-w-4xl rounded-[2rem] object-cover shadow-xl" width="1200" height="750">@endif
</header>

<main class="px-5 py-20">
    <ol class="relative mx-auto max-w-4xl">
        <span class="absolute bottom-0 left-4 top-0 w-0.5 md:left-1/2" style="background: repeating-linear-gradient(to bottom, var(--t-primary) 0 8px, transparent 8px 16px); opacity: .35" aria-hidden="true"></span>
        @foreach ($d->items('timeline') as $i => $m)
            @php $img = $photos[$i] ?? null; $left = $i % 2 === 0; @endphp
            <li class="t-reveal relative mb-14 pl-12 md:mb-20 md:grid md:grid-cols-2 md:gap-14 md:pl-0">
                <span class="bg-accent absolute left-[0.55rem] top-2 h-4 w-4 rounded-full ring-4 ring-[var(--t-bg)] md:left-1/2 md:-translate-x-1/2"></span>
                <div class="{{ $left ? 'md:text-right' : 'md:order-2' }}">
                    <p class="font-accent text-2xl t-accent">{{ $m['date'] ?? '' }}</p>
                    <h2 class="font-heading mt-1 text-3xl t-primary">{{ $m['title'] ?? '' }}</h2>
                    <p class="mt-2 text-lg text-soft">{!! \App\Support\TemplateData::lines($m['text'] ?? '') !!}</p>
                </div>
                @if ($img)
                    <figure class="mt-5 md:mt-0 {{ $left ? '' : 'md:order-1' }}">
                        <img src="{{ $img['url'] }}" alt="{{ $m['title'] ?? '' }}" loading="lazy" class="aspect-[4/3] w-full rounded-xl bg-white object-cover p-2 shadow-lg {{ $left ? 'rotate-1' : '-rotate-1' }}">
                    </figure>
                @endif
            </li>
        @endforeach
    </ol>

    @if (count($photos) > count($d->items('timeline')))
        <section class="mx-auto mt-6 max-w-5xl">@include('page-templates.partials.gallery', ['items' => array_slice($photos, count($d->items('timeline'))), 'style' => 'polaroid'])</section>
    @endif

    @if ($d->has('closing_message'))
        <section class="t-reveal mx-auto mt-20 max-w-2xl text-center">
            <p class="font-heading text-4xl leading-snug t-primary sm:text-5xl">{{ $d->text('closing_message') }}</p>
            <p class="font-accent mt-4 text-3xl t-accent">— {{ $d->text('your_name') }}</p>
        </section>
    @endif
</main>
@endsection
