@extends('page-templates._base')
@section('body')
@php $photo = $d->image('surprise_photo'); $notes = $d->items('open_when'); $colors = ['#FFD166', '#EF476F', '#06D6A0', '#118AB2', '#F78C6B', '#9B5DE5']; @endphp
<main class="t-grain relative min-h-dvh overflow-hidden px-5 pb-24 pt-16 text-center" data-confetti="#e63946,#ffd166,#06d6a0,#118ab2,#ffffff">
    @include('page-templates.partials.particles', ['chars' => ['✦', '✧', '★'], 'count' => 14, 'class' => 't-accent'])

    <section class="relative mx-auto max-w-xl">
        <p class="font-accent text-3xl t-primary" {{ $d->f('to_name') }}>প্রিয় {{ $d->text('to_name') }},</p>
        <h1 class="font-heading mt-2 text-5xl leading-tight sm:text-6xl" data-split {{ $d->f('teaser') }}>{{ $d->text('teaser', 'তোমার জন্য একটা সারপ্রাইজ!') }}</h1>
        <div class="mt-14">
            <button type="button" class="t-giftbox" data-giftbox="#inside" aria-label="গিফট বক্স খোলো" aria-controls="inside">
                <span class="t-giftbox__lid"></span><span class="t-giftbox__box"></span><span class="t-giftbox__ribbon-v"></span><span class="t-giftbox__bow">🎀</span>
            </button>
            <p class="mt-6 animate-pulse text-sm font-semibold text-soft">👆 বক্সটা ট্যাপ করো</p>
        </div>
    </section>

    <div id="inside" hidden>
        <section class="t-reveal relative mx-auto mt-16 max-w-md" {{ $d->f('surprise surprise_photo') }}>
            <p class="font-heading text-3xl t-primary">আসল সারপ্রাইজ 👇</p>
            <div class="t-scratch mt-6 overflow-hidden rounded-[1.6rem] bg-white shadow-[0_30px_60px_-30px_rgba(0,0,0,.4)]" data-scratch="আঙুল দিয়ে ঘষো ✨">
                @if ($photo)<img src="{{ $photo['url'] }}" alt="" class="aspect-[4/3] w-full object-cover">@endif
                <div class="px-6 py-7"><p class="font-heading text-2xl leading-snug t-primary sm:text-3xl">{!! \App\Support\TemplateData::lines($d->text('surprise')) !!}</p></div>
                <button type="button" data-scratch-skip>সরাসরি দেখো</button>
            </div>
        </section>

        @if ($notes)
            <section class="mx-auto mt-24 max-w-4xl" {{ $d->f('open_when') }}>
                <h2 class="font-heading text-4xl t-primary sm:text-5xl">যখন… তখন খুলো 💌</h2>
                <p class="mt-2 text-soft">খামের উপর ট্যাপ করো</p>
                <div class="mt-10 grid gap-6 sm:grid-cols-2">
                    @foreach ($notes as $i => $n)
                        <button type="button" class="t-flip t-reveal" data-flip aria-pressed="false" style="--i: {{ $i }}">
                            <span class="t-flip__inner min-h-[12rem]">
                                <span class="t-flip__face grid place-items-center p-6 text-white shadow-lg" style="background: {{ $colors[$i % count($colors)] }}">
                                    <span><span class="block text-4xl">✉️</span><span class="font-heading mt-2 block text-2xl leading-snug">{{ $n['when'] ?? '' }}</span></span>
                                </span>
                                <span class="t-flip__face t-flip__back grid place-items-center bg-white p-6 text-lg leading-relaxed shadow-lg">{{ $n['message'] ?? '' }}</span>
                            </span>
                        </button>
                    @endforeach
                </div>
            </section>
        @endif

    @include('page-templates.partials.surprises')
        @if ($d->has('closing_message'))
            <section class="t-reveal mx-auto mt-24 max-w-xl rounded-[2rem] bg-primary px-8 py-12 text-white" {{ $d->f('closing_message from_name') }}>
                <div class="t-prose text-xl leading-loose">{{ $d->para('closing_message') }}</div>
                @if ($d->has('from_name'))<p class="font-accent mt-6 text-3xl">— {{ $d->text('from_name') }}</p>@endif
                <button type="button" class="t-btn mt-8 bg-white t-primary" data-burst="🎉">🎉 থ্যাংক ইউ পাঠাও</button>
            </section>
        @endif
    </div>
</main>
@endsection
