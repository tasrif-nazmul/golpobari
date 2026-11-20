@extends('page-templates._base')
@section('body')
@php $hero = $d->image('hero_photo'); $date = $d->date('event_date'); @endphp
<main class="px-4 py-10 sm:py-16">
    <article class="mx-auto max-w-2xl border-[3px] p-2 sm:p-3" style="border-color: var(--t-accent)">
        <div class="border px-5 py-12 text-center sm:px-12" style="border-color: color-mix(in oklab, var(--t-accent) 60%, transparent)">
            <p class="font-accent t-reveal text-3xl t-accent">শুভ আমন্ত্রণ</p>
            @if ($d->has('hosts'))<p class="t-reveal mt-3 text-soft">{{ $d->text('hosts') }}-এর পক্ষ থেকে</p>@endif
            <h1 class="font-heading t-reveal mt-4 text-5xl font-bold leading-tight t-primary sm:text-6xl">{{ $d->text('event_title') }}</h1>

            @if ($hero)<img src="{{ $hero['url'] }}" alt="" class="t-reveal mx-auto mt-10 aspect-[4/5] w-full max-w-sm rounded-t-full object-cover" width="480" height="600">@endif

            <div class="t-reveal mt-10 text-lg leading-loose">{{ $d->para('message') }}</div>

            @if ($date)
                <div class="t-reveal mx-auto mt-10 flex max-w-sm items-center justify-center gap-5">
                    <div class="h-px flex-1 bg-accent"></div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-soft">{{ $date->locale('bn')->translatedFormat('l') }}</p>
                        <p class="font-heading text-7xl font-bold leading-none t-primary">{{ \App\Support\Bn::digits($date->day) }}</p>
                        <p class="text-lg font-semibold">{{ \App\Support\Bn::date($date) }}</p>
                    </div>
                    <div class="h-px flex-1 bg-accent"></div>
                </div>
                @if ($d->has('event_time'))<p class="mt-2 text-xl font-semibold t-primary">{{ $d->text('event_time') }}</p>@endif

                <div class="t-reveal mx-auto mt-8 max-w-xs" data-countdown="{{ $date->toDateString() }}">
                    <div data-countdown-out class="grid grid-cols-4 gap-2">
                        @foreach (['d' => 'দিন', 'h' => 'ঘণ্টা', 'm' => 'মিনিট', 's' => 'সেকেন্ড'] as $k => $l)
                            <div class="mix-accent rounded-lg py-2"><p class="font-heading text-2xl font-bold t-primary" data-cd="{{ $k }}">০০</p><p class="text-[11px] text-soft">{{ $l }}</p></div>
                        @endforeach
                    </div>
                    <p data-countdown-done hidden class="font-heading text-2xl t-primary">আজই অনুষ্ঠান!</p>
                </div>
            @endif

            <div class="t-reveal mt-12">
                <p class="text-sm uppercase tracking-[0.3em] text-soft">ভেন্যু</p>
                <p class="mt-2 text-xl font-semibold">{!! \App\Support\TemplateData::lines($d->text('venue')) !!}</p>
                @if ($d->link($d->text('map_link')))<a href="{{ $d->link($d->text('map_link')) }}" target="_blank" rel="noopener nofollow" class="t-btn t-btn--primary mt-5">ম্যাপে দেখুন</a>@endif
            </div>

            @if ($d->has('schedule'))
                <div class="t-reveal mx-auto mt-12 max-w-sm text-left">
                    <p class="text-center text-sm uppercase tracking-[0.3em] text-soft">অনুষ্ঠানসূচি</p>
                    <ol class="mt-4 divide-y" style="border-color: color-mix(in oklab, var(--t-accent) 40%, transparent)">
                        @foreach ($d->items('schedule') as $s)
                            <li class="flex gap-5 py-3"><span class="w-16 shrink-0 font-semibold t-primary">{{ $s['time'] ?? '' }}</span><span>{{ $s['title'] ?? '' }}</span></li>
                        @endforeach
                    </ol>
                </div>
            @endif

            @if ($d->has('dress_code'))<p class="t-reveal mt-10"><span class="text-soft">ড্রেস কোড:</span> <b>{{ $d->text('dress_code') }}</b></p>@endif

            @if ($d->gallery('gallery'))<div class="mt-12">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'grid'])</div>@endif

            @if ($d->has('rsvp_phone'))
                <div class="t-reveal mt-12">
                    <p class="text-soft">উপস্থিতি নিশ্চিত করুন</p>
                    <div class="mt-3 flex flex-wrap justify-center gap-3">
                        <a href="tel:{{ $d->tel($d->text('rsvp_phone')) }}" class="t-btn t-btn--primary">কল করুন</a>
                        @if ($wa = $d->whatsapp($d->text('rsvp_phone')))<a href="{{ $wa }}" target="_blank" rel="noopener" class="t-btn t-btn--ghost t-primary">WhatsApp</a>@endif
                    </div>
                </div>
            @endif
        </div>
    </article>
</main>
@endsection
