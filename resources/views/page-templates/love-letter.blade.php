@extends('page-templates._base')
@section('body')
@php $photo = $d->image('photo'); $date = $d->date('letter_date'); @endphp
@include('page-templates.partials.gate', [
    'style' => 'envelope', 'icon' => '✉️', 'mode' => 'hearts', 'initial' => mb_substr($d->text('from_name'), 0, 1),
    'eyebrow' => 'ডাকে একটা চিঠি এসেছে', 'title' => $d->text('to_name').'-এর জন্য',
    'btn' => 'সিলমোহর ছুঁয়ে চিঠিটা খোলো',
])
<main class="t-grain relative min-h-dvh overflow-hidden px-4 py-14 sm:py-20" style="background-image: radial-gradient(color-mix(in oklab, var(--t-text) 7%, transparent) 1px, transparent 1px); background-size: 18px 18px">
    @include('page-templates.partials.particles', ['chars' => ['❀', '✿', '❁'], 'count' => 10, 'class' => 't-accent opacity-60'])

    <section class="relative text-center">
        <p class="t-eyebrow text-soft">ডাকঘর · {{ $date ? \App\Support\Bn::date($date) : 'আজ' }}</p>
        <p class="font-heading t-reveal mt-4 text-3xl t-primary">একটা চিঠি এসেছে</p>
        <h1 class="font-heading mt-1 text-5xl t-primary sm:text-7xl" data-split><span {{ $d->f('to_name') }}>{{ $d->text('to_name') }}</span>-এর জন্য</h1>

        <div class="t-envelope mt-14" data-envelope="#letter" role="button" tabindex="0" aria-label="খাম খুলুন" aria-controls="letter">
            <div class="t-envelope__back"></div>
            <div class="t-envelope__peek"></div>
            <div class="t-envelope__front"></div>
            <div class="t-envelope__flap"></div>
            <div class="t-envelope__seal">♥</div>
            <span class="absolute right-[6%] top-[8%] z-[6] grid h-14 w-12 place-items-center border-2 border-dashed border-white/60 text-2xl" aria-hidden="true">🌸</span>
        </div>
        <p class="font-heading mt-6 text-2xl t-primary" {{ $d->f('from_name') }}>প্রেরক: {{ $d->text('from_name') }}</p>
        <p class="mt-6 animate-pulse text-sm font-semibold text-soft">👆 খামটি ছুঁয়ে খুলুন</p>
    </section>

    <article id="letter" hidden class="t-letter relative mx-auto mt-20 max-w-2xl rounded-sm px-6 pb-16 pt-12 sm:px-14">
        <span class="t-tape -top-3 left-8 -rotate-6" aria-hidden="true"></span>
        <span class="t-tape -top-3 right-8 rotate-6" aria-hidden="true"></span>
        @if ($date)<p class="text-right text-sm text-soft" {{ $d->f('letter_date') }}>{{ \App\Support\Bn::date($date) }}</p>@endif
        <p class="font-heading mt-4 text-4xl t-primary sm:text-5xl" {{ $d->f('opening') }}>{{ $d->text('opening', 'প্রিয় '.$d->text('to_name').',') }}</p>
        <div class="t-prose mt-4 text-[1.15rem]" {{ $d->f('letter') }}>
            @foreach (preg_split("/\n{2,}/", $d->text('letter')) as $p)
                <p data-type>{{ trim($p) }}</p>
            @endforeach
        </div>
        <p class="font-heading mt-10 text-right text-3xl t-primary sm:text-4xl" {{ $d->f('signature') }}>{{ $d->text('signature', 'ইতি,') }}<br>{{ $d->text('from_name') }}</p>
        @if ($d->has('ps'))<p class="mt-8 text-[1.05rem]" {{ $d->f('ps') }}><b class="font-heading text-2xl t-primary">পুনশ্চ:</b> {{ $d->text('ps') }}</p>@endif
        @if ($photo)
            <figure class="relative mx-auto mt-12 w-56 rotate-[-4deg] bg-white p-3 pb-10 shadow-xl sm:absolute sm:-right-20 sm:top-28 sm:mt-0 sm:w-48" {{ $d->f('photo') }}>
                <span class="t-tape -top-3 left-1/2 -translate-x-1/2 rotate-3" aria-hidden="true"></span>
                <img src="{{ $photo['url'] }}" alt="" class="aspect-square w-full object-cover" loading="lazy">
                <figcaption class="font-heading absolute bottom-2 left-0 right-0 text-center text-xl t-primary">আমরা ♥</figcaption>
            </figure>
        @endif
        <div class="mt-12 text-center">
            <button type="button" class="t-btn t-btn--primary" data-burst="💋">💋 একটা চুমু পাঠাও</button>
        </div>
    </article>
</main>
@endsection
