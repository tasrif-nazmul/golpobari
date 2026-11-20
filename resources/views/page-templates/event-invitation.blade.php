@extends('page-templates._base')
@section('body')
@php $hero = $d->image('hero_photo'); $date = $d->date('event_date'); @endphp
@include('page-templates.partials.gate', [
    'icon' => '💌', 'mode' => 'confetti', 'eyebrow' => 'শুভ আমন্ত্রণ',
    'title' => $d->text('event_title'),
    'sub' => 'আপনার জন্য একটি দাওয়াতপত্র',
    'btn' => 'দাওয়াতপত্র খুলুন',
])
<main class="t-grain relative overflow-hidden px-3 py-8 sm:py-16" data-confetti="#e8b04b,#6b2d5c,#ffffff,#d77a61">
    @include('page-templates.partials.particles', ['chars' => ['✿', '❀', '❁'], 'count' => 12, 'class' => 't-accent opacity-70'])

    <article class="t-nakshi relative mx-auto max-w-2xl overflow-hidden rounded-[2rem] bg-white/60 px-5 pb-14 pt-16 text-center shadow-[0_40px_80px_-50px_rgba(0,0,0,.5)] backdrop-blur-sm sm:px-14">
        @foreach (['-left-16 -top-16', '-right-16 -top-16', '-left-16 -bottom-16', '-right-16 -bottom-16'] as $corner)
            <div class="pointer-events-none absolute {{ $corner }} h-40 w-40 opacity-40">@include('page-templates.partials.alpona', ['rings' => 2, 'petals' => 8, 'class' => 'h-full w-full'])</div>
        @endforeach

        <div {{ $d->f('event_title hosts') }}>
            <p class="font-accent t-reveal text-4xl t-accent">শুভ আমন্ত্রণ</p>
            @if ($d->has('hosts'))<p class="t-reveal mt-3 text-soft">{{ $d->text('hosts') }}-এর পক্ষ থেকে সবিনয় নিবেদন</p>@endif
            <h1 class="font-heading mt-5 text-5xl font-bold leading-tight t-primary sm:text-7xl" data-split>{{ $d->text('event_title') }}</h1>
        </div>

        @if ($hero)
            <div class="t-reveal relative mx-auto mt-12 w-full max-w-xs" {{ $d->f('hero_photo') }}>
                <div class="absolute -inset-14 t-alpona--spin opacity-50">@include('page-templates.partials.alpona', ['rings' => 3, 'petals' => 10, 'class' => 'h-full w-full'])</div>
                <div class="t-arch relative aspect-[4/5] border-[6px] shadow-2xl" style="border-color: var(--t-accent)">
                    <img src="{{ $hero['url'] }}" alt="" class="h-full w-full object-cover" width="480" height="600">
                </div>
            </div>
        @endif

        <div class="t-reveal t-prose mx-auto mt-14 max-w-lg text-lg leading-loose" {{ $d->f('message') }}>{{ $d->para('message') }}</div>

        @if ($date)
            <div class="t-reveal mx-auto mt-12 flex max-w-sm items-center justify-center gap-5" {{ $d->f('event_date') }}>
                <div class="h-px flex-1 bg-accent"></div>
                <div class="rounded-3xl bg-primary px-7 py-5 text-white shadow-xl">
                    <p class="text-sm tracking-widest text-white/75">{{ $date->locale('bn')->translatedFormat('l') }}</p>
                    <p class="font-heading text-7xl font-bold leading-none t-accent">{{ \App\Support\Bn::digits($date->day) }}</p>
                    <p class="text-lg font-semibold">{{ $date->locale('bn')->translatedFormat('F') }} {{ \App\Support\Bn::digits($date->year) }}</p>
                </div>
                <div class="h-px flex-1 bg-accent"></div>
            </div>
            @if ($d->has('event_time'))<p class="mt-4 text-xl font-semibold t-primary" {{ $d->f('event_time') }}>🕖 {{ $d->text('event_time') }}</p>@endif

            <div class="t-reveal mx-auto mt-8 max-w-xs" data-countdown="{{ $date->toDateString() }}">
                <div data-countdown-out class="grid grid-cols-4 gap-2">
                    @foreach (['d' => 'দিন', 'h' => 'ঘণ্টা', 'm' => 'মিনিট', 's' => 'সেকেন্ড'] as $k => $l)
                        <div class="mix-accent rounded-xl py-2"><p class="font-heading text-2xl font-bold t-primary" data-cd="{{ $k }}">০০</p><p class="text-[11px] text-soft">{{ $l }}</p></div>
                    @endforeach
                </div>
                <p data-countdown-done hidden class="font-heading text-2xl t-primary">আজই অনুষ্ঠান! 🎉</p>
            </div>
        @endif

        <div class="t-reveal mx-auto mt-14 max-w-md rounded-3xl border-2 border-dashed px-6 py-8" style="border-color: var(--t-accent)" {{ $d->f('venue map_link') }}>
            <p class="text-3xl" aria-hidden="true">📍</p>
            <p class="t-eyebrow mt-2 text-soft">ভেন্যু</p>
            <p class="mt-2 text-xl font-semibold">{!! \App\Support\TemplateData::lines($d->text('venue')) !!}</p>
            @if ($d->link($d->text('map_link')))<a href="{{ $d->link($d->text('map_link')) }}" target="_blank" rel="noopener nofollow" class="t-btn t-btn--primary mt-5">🗺️ ম্যাপে দেখুন</a>@endif
        </div>

        @if ($d->has('schedule'))
            <div class="t-reveal mx-auto mt-14 max-w-sm text-left" {{ $d->f('schedule') }}>
                <p class="font-accent text-center text-3xl t-accent">অনুষ্ঠানসূচি</p>
                <ol class="relative mt-6 space-y-5 border-l-2 pl-6" style="border-color: var(--t-accent)">
                    @foreach ($d->items('schedule') as $i => $s)
                        <li class="t-reveal relative" style="--i: {{ $i }}">
                            <span class="absolute -left-[2.05rem] top-1 grid h-5 w-5 place-items-center rounded-full bg-accent ring-4 ring-white"></span>
                            <span class="block text-sm font-bold t-primary">{{ $s['time'] ?? '' }}</span>
                            <span class="text-lg">{{ $s['title'] ?? '' }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        @if ($d->has('dress_code'))<p class="t-reveal mt-10 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 shadow" {{ $d->f('dress_code') }}>👗 <span class="text-soft">ড্রেস কোড:</span> <b>{{ $d->text('dress_code') }}</b></p>@endif

        @if ($d->gallery('gallery'))<div class="mt-14" {{ $d->f('gallery') }}>@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'polaroid'])</div>@endif

        @if ($d->has('rsvp_phone'))
            <div class="t-reveal mt-14" {{ $d->f('rsvp_phone') }}>
                <p class="font-accent text-3xl t-accent">আপনার উপস্থিতি কামনা করি</p>
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <a href="tel:{{ $d->tel($d->text('rsvp_phone')) }}" class="t-btn t-btn--primary">📞 কল করুন</a>
                    @if ($wa = $d->whatsapp($d->text('rsvp_phone')))<a href="{{ $wa }}" target="_blank" rel="noopener" class="t-btn t-btn--ghost t-primary">💬 WhatsApp</a>@endif
                </div>
            </div>
        @endif
    </article>
</main>
@endsection
