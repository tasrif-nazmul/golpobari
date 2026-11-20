@extends('page-templates._base')
@section('body')
@php
    $hero = $d->image('hero_photo');
    $bday = $d->date('birthday_date');
    $colors = ['#ffb703', '#e9839f', '#8ecae6', '#90be6d', '#f4a261'];
@endphp
<header class="relative overflow-hidden px-5 pb-14 pt-12 text-center sm:pt-16" data-confetti="#ffb703,#e9839f,#2b2d8f,#90be6d,#ffffff">
    @foreach ([[6, 12, '#ffb703'], [88, 8, '#e9839f'], [14, 70, '#8ecae6'], [80, 62, '#90be6d'], [50, 4, '#2b2d8f']] as [$x, $y, $c])
        <span aria-hidden="true" class="absolute h-3 w-3 rotate-12 rounded-sm" style="left: {{ $x }}%; top: {{ $y }}%; background: {{ $c }}"></span>
    @endforeach
    <p class="font-heading t-reveal text-2xl font-bold t-accent">🎉 শুভ জন্মদিন 🎉</p>
    <h1 class="font-heading t-reveal mt-2 text-[4.2rem] font-bold leading-none t-primary sm:text-9xl">{{ $d->text('birthday_name') }}</h1>
    @if ($d->has('age'))<p class="t-reveal mt-3 inline-block rounded-full bg-primary px-5 py-1.5 text-lg font-bold text-white">আজ {{ \App\Support\Bn::digits($d->text('age')) }}-এ পা দিল!</p>@endif

    @if ($hero)
        <div class="t-reveal relative mx-auto mt-10 w-72 sm:w-96">
            <div class="bg-accent absolute inset-0 translate-x-3 translate-y-3 rounded-[42%_58%_55%_45%/48%_42%_58%_52%]"></div>
            <img src="{{ $hero['url'] }}" alt="{{ $d->text('birthday_name') }}" class="relative aspect-square w-full rounded-[42%_58%_55%_45%/48%_42%_58%_52%] object-cover" width="384" height="384">
            <span class="absolute -right-2 -top-4 text-5xl" aria-hidden="true">🎂</span>
        </div>
    @endif

    @if ($bday)
        <div class="t-reveal mx-auto mt-10 max-w-md" data-countdown="{{ $bday->toDateString() }}" data-recurring="1">
            <div data-countdown-out>
                <p class="text-sm font-semibold uppercase tracking-widest text-soft">জন্মদিন আসতে বাকি</p>
                <div class="mt-3 grid grid-cols-4 gap-2">
                    @foreach (['d' => 'দিন', 'h' => 'ঘণ্টা', 'm' => 'মিনিট', 's' => 'সেকেন্ড'] as $k => $l)
                        <div class="rounded-2xl bg-white py-3 shadow-sm"><p class="font-heading text-3xl font-bold t-primary" data-cd="{{ $k }}">০০</p><p class="text-xs text-soft">{{ $l }}</p></div>
                    @endforeach
                </div>
            </div>
            <p data-countdown-done hidden class="font-heading text-3xl font-bold t-primary">আজই সেই দিন! 🥳</p>
            <p class="mt-3 text-soft">{{ \App\Support\Bn::date($bday) }}</p>
        </div>
    @endif
</header>

<main class="px-5 pb-16">
    <section class="t-reveal mx-auto max-w-2xl rounded-[2rem] bg-white p-7 text-lg shadow-[0_20px_50px_-25px_rgba(43,45,143,.45)] sm:p-10">
        <div class="t-prose leading-loose">{{ $d->para('message') }}</div>
        @if ($d->has('from_name'))<p class="font-heading mt-6 text-right text-2xl font-bold t-primary">— {{ $d->text('from_name') }}</p>@endif
    </section>

    @if ($d->has('memories'))
        <section class="mx-auto mt-16 max-w-4xl">
            <h2 class="font-heading text-center text-4xl font-bold t-primary">মনে আছে? 💭</h2>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                @foreach ($d->items('memories') as $i => $m)
                    <div class="t-reveal mix-accent rounded-3xl p-6" style="--i: {{ $i }}"><h3 class="text-xl font-bold t-primary">{{ $m['title'] ?? '' }}</h3><p class="mt-1 text-soft">{{ $m['text'] ?? '' }}</p></div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-16 max-w-5xl">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'grid'])</section>
    @endif

    @if ($d->has('wishes'))
        <section class="mx-auto mt-16 max-w-5xl">
            <h2 class="font-heading text-center text-4xl font-bold t-primary">সবার শুভেচ্ছা 💌</h2>
            <div class="mt-8 columns-1 gap-4 sm:columns-2 lg:columns-3">
                @foreach ($d->items('wishes') as $i => $w)
                    <div class="t-reveal mb-4 break-inside-avoid rounded-md p-5 shadow-md" style="background: {{ $colors[$i % count($colors)] }}33; transform: rotate({{ [-1.5, 1, -0.5, 1.5][$i % 4] }}deg); --i: {{ $i }}">
                        <p class="text-lg">{{ $w['message'] ?? '' }}</p>
                        <p class="mt-3 font-bold t-primary">— {{ $w['from'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection
