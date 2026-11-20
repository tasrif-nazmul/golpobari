@extends('page-templates._base')
@section('body')
@php
    $hero = $d->image('hero_photo');
    $bday = $d->date('birthday_date');
    $colors = ['#ffb703', '#e9839f', '#8ecae6', '#90be6d', '#f4a261', '#cdb4db'];
    $age = $d->text('age');
    $candles = max(1, min(7, (int) ($age ? strlen(preg_replace('/\D/', '', strtr($age, ['০'=>'0','১'=>'1','২'=>'2','৩'=>'3','৪'=>'4','৫'=>'5','৬'=>'6','৭'=>'7','৮'=>'8','৯'=>'9']))) + 2 : 3)));
@endphp
@include('page-templates.partials.gate', [
    'style' => 'gift', 'mode' => 'confetti', 'eyebrow' => 'শুভ জন্মদিন',
    'title' => $d->text('birthday_name').', তোমার জন্য',
    'sub' => $d->text('from_name') ? 'ইতি, '.$d->text('from_name') : 'ভালোবাসাসহ',
    'btn' => 'ফিতে টেনে উপহার খোলো', 'paper' => '#e2577a', 'ribbon' => '#f3c44a', 'bg' => '#2b2d6e',
])

<header class="relative overflow-hidden px-5 pb-16 pt-14 text-center sm:pt-20" data-confetti="#ffb703,#e9839f,#2b2d8f,#90be6d,#ffffff">
    @foreach ([[4, 20, '#e9839f', 0], [84, 10, '#ffb703', 1.5], [10, 62, '#8ecae6', 3], [86, 58, '#90be6d', 2], [70, 28, '#cdb4db', 4]] as [$x, $y, $c, $delay])
        <span aria-hidden="true" class="t-balloon" style="left: {{ $x }}%; top: {{ $y }}%; background: {{ $c }}; color: {{ $c }}; animation-delay: -{{ $delay }}s"></span>
    @endforeach

    <p class="font-heading t-reveal relative inline-block -rotate-3 rounded-full bg-accent px-5 py-1 text-xl font-bold text-[#1d1f4e]">🎉 শুভ জন্মদিন 🎉</p>
    <h1 class="font-heading relative mt-4 text-[4.4rem] font-extrabold leading-[.95] t-primary sm:text-9xl" data-split {{ $d->f('birthday_name') }}>{{ $d->text('birthday_name') }}</h1>

    @if ($hero)
        <div class="t-reveal relative mx-auto mt-10 w-72 sm:w-96" {{ $d->f('hero_photo') }}>
            <div class="bg-accent t-blob absolute inset-0 translate-x-3 translate-y-3"></div>
            <div data-tilt class="relative transition-transform"><img src="{{ $hero['url'] }}" alt="{{ $d->text('birthday_name') }}" class="t-blob relative aspect-square w-full object-cover" width="384" height="384"></div>
            @if ($age)
                <span class="font-heading absolute -right-3 -top-4 grid h-24 w-24 rotate-12 place-items-center rounded-full bg-primary text-white shadow-xl" {{ $d->f('age') }}>
                    <span><span class="block text-4xl font-extrabold leading-none">{{ \App\Support\Bn::digits($age) }}</span><span class="text-xs">বছর</span></span>
                </span>
            @endif
        </div>
    @endif

    @if ($bday)
        <div class="t-reveal relative mx-auto mt-12 max-w-md" data-countdown="{{ $bday->toDateString() }}" data-recurring="1" {{ $d->f('birthday_date') }}>
            <div data-countdown-out>
                <p class="text-sm font-semibold uppercase tracking-widest text-soft">জন্মদিন আসতে বাকি</p>
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach (['d' => 'দিন', 'h' => 'ঘণ্টা', 'm' => 'মিনিট', 's' => 'সেকেন্ড'] as $k => $l)
                        <div class="rounded-2xl bg-white py-3 shadow-[0_8px_0_-2px_var(--t-accent)]"><p class="font-heading text-3xl font-bold t-primary" data-cd="{{ $k }}">০০</p><p class="text-xs text-soft">{{ $l }}</p></div>
                    @endforeach
                </div>
            </div>
            <p data-countdown-done hidden class="font-heading text-3xl font-bold t-primary">আজই সেই দিন! 🥳</p>
            <p class="mt-3 text-soft">{{ \App\Support\Bn::date($bday) }}</p>
        </div>
    @endif
</header>

<main class="px-5 pb-16">
    {{-- Blow the candles to read the message --}}
    <section class="mx-auto max-w-2xl text-center" {{ $d->f('message from_name') }}>
        <p class="font-heading text-3xl font-bold t-primary">উইশ করো, তারপর ফুঁ দাও! 🌬️</p>
        <p class="mt-1 text-sm text-soft">মোমবাতিগুলো ট্যাপ করে নেভাও</p>
        <div class="t-cake mt-6" data-candles="#bday-wish" role="button" tabindex="0" aria-label="মোমবাতি নেভাও">
            <div class="t-cake__candles">
                @for ($c = 0; $c < $candles; $c++)<span class="t-candle" data-flame><i></i></span>@endfor
            </div>
            <div class="t-cake__tier h-14 w-44" style="background: var(--t-accent)"></div>
            <div class="t-cake__tier h-16 w-56" style="background: color-mix(in oklab, var(--t-primary) 80%, #fff)"></div>
            <div class="mx-auto h-3 w-64 rounded-full bg-black/10"></div>
        </div>
        <div id="bday-wish" @if (empty($isDraft)) hidden @endif class="t-reveal mt-10 rounded-[2rem] bg-white p-7 text-left text-lg shadow-[0_20px_50px_-25px_rgba(43,45,143,.45)] sm:p-10">
            <div class="t-prose leading-loose">{{ $d->para('message') }}</div>
            @if ($d->has('from_name'))<p class="font-heading mt-6 text-right text-2xl font-bold t-primary">— {{ $d->text('from_name') }}</p>@endif
        </div>
    </section>

    @if ($d->has('memories'))
        <section class="mx-auto mt-24 max-w-4xl" {{ $d->f('memories') }}>
            <h2 class="font-heading text-center text-4xl font-bold t-primary sm:text-5xl">মনে আছে? 💭</h2>
            <div class="mt-10 grid gap-5 sm:grid-cols-2">
                @foreach ($d->items('memories') as $i => $m)
                    <div class="t-reveal relative overflow-hidden rounded-3xl p-6 pt-8" style="--i: {{ $i }}; background: {{ $colors[$i % count($colors)] }}40">
                        <span class="font-heading absolute -right-1 -top-4 text-7xl font-extrabold opacity-15 t-primary">{{ \App\Support\Bn::digits($i + 1) }}</span>
                        <h3 class="text-xl font-bold t-primary">{{ $m['title'] ?? '' }}</h3><p class="mt-1 text-soft">{{ $m['text'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-20 max-w-5xl" {{ $d->f('gallery') }}>
            <h2 class="font-heading mb-8 text-center text-4xl font-bold t-primary">📸 ছবির পাতা</h2>
            @include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'masonry'])
        </section>
    @endif

    @if ($d->has('wishes'))
        <section class="mx-auto mt-24 max-w-5xl" {{ $d->f('wishes') }}>
            <h2 class="font-heading text-center text-4xl font-bold t-primary sm:text-5xl">সবার শুভেচ্ছা 💌</h2>
            <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($d->items('wishes') as $i => $w)
                    <div class="t-note t-reveal" style="--i: {{ $i }}; background: {{ $colors[$i % count($colors)] }}">
                        <p class="font-accent text-xl leading-snug text-[#1d1f4e]">{{ $w['message'] ?? '' }}</p>
                        <p class="mt-3 text-right font-bold text-[#1d1f4e]">— {{ $w['from'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="mt-20 text-center"><button type="button" class="t-btn t-btn--primary" onclick="window.gbConfetti && window.gbConfetti()">🎊 আরেকবার সেলিব্রেট!</button></div>
</main>
@endsection
