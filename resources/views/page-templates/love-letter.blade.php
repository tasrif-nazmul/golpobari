@extends('page-templates._base')
@section('body')
@php $photo = $d->image('photo'); $date = $d->date('letter_date'); @endphp
<main class="min-h-dvh px-4 py-14 sm:py-20" style="background-image: radial-gradient(color-mix(in oklab, var(--t-text) 6%, transparent) 1px, transparent 1px); background-size: 18px 18px">
    <section class="text-center">
        <p class="font-accent text-3xl t-primary">একটা চিঠি এসেছে</p>
        <h1 class="font-heading mt-1 text-5xl t-primary sm:text-6xl">{{ $d->text('to_name') }}-এর জন্য</h1>
        <div class="t-envelope mt-12" data-envelope="#letter" role="button" tabindex="0" aria-label="খাম খুলুন" aria-controls="letter">
            <div class="t-envelope__back"></div>
            <div class="t-envelope__peek"></div>
            <div class="t-envelope__front"></div>
            <div class="t-envelope__flap"></div>
            <div class="t-envelope__seal">♥</div>
        </div>
        <p class="mt-8 animate-pulse text-sm font-semibold text-soft">খামটি ছুঁয়ে খুলুন</p>
    </section>

    <article id="letter" hidden class="t-letter relative mx-auto mt-16 max-w-2xl rounded-sm px-6 pb-14 pt-10 sm:px-14">
        @if ($date)<p class="text-right text-sm text-soft">{{ \App\Support\Bn::date($date) }}</p>@endif
        <p class="font-heading mt-4 text-4xl t-primary">{{ $d->text('opening', 'প্রিয় '.$d->text('to_name').',') }}</p>
        <div class="t-prose mt-4 text-[1.15rem]">{{ $d->para('letter') }}</div>
        <p class="font-heading mt-10 text-right text-3xl t-primary">{{ $d->text('signature', 'ইতি,') }}<br>{{ $d->text('from_name') }}</p>
        @if ($d->has('ps'))<p class="mt-8 text-[1.05rem]"><b class="t-primary">P.S.</b> {{ $d->text('ps') }}</p>@endif
        @if ($photo)
            <figure class="relative mx-auto mt-12 w-56 rotate-[-4deg] bg-white p-3 pb-10 shadow-xl sm:absolute sm:-right-16 sm:top-24 sm:mt-0 sm:w-48">
                <span class="absolute -top-3 left-1/2 h-6 w-16 -translate-x-1/2 rotate-3 bg-[var(--t-accent)] opacity-60" aria-hidden="true"></span>
                <img src="{{ $photo['url'] }}" alt="" class="aspect-square w-full object-cover" loading="lazy">
            </figure>
        @endif
    </article>
</main>
@endsection
