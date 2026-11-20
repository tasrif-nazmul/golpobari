@extends('page-templates._base')
@section('body')
@php $cover = $d->image('cover_photo'); $travelers = $d->list('travelers'); $stamps = ['✈', '⛰', '🌊', '🏕', '🚂', '🛶']; @endphp
@include('page-templates.partials.gate', [
    'style' => 'book', 'eyebrow' => 'ভ্রমণের খাতা', 'title' => $d->text('trip_title'),
    'sub' => $d->text('from_place').' → '.$d->text('to_place'), 'btn' => 'ডায়েরিটা খোলো', 'flower' => false, 'ribbon' => '#d9a441',
])
<header class="relative overflow-hidden px-4 pb-14 pt-10 sm:pt-16">
    <div class="pointer-events-none absolute inset-x-0 top-0 h-80" style="background: radial-gradient(70% 100% at 50% 0%, color-mix(in oklab, var(--t-accent) 35%, transparent), transparent)"></div>

    <div class="t-pass t-reveal relative mx-auto max-w-xl overflow-hidden">
        <div class="flex items-center justify-between bg-primary px-6 py-3 text-white">
            <span class="t-eyebrow t-eyebrow--wide">Boarding pass</span><span class="text-sm opacity-80">GolpoBari Air ✈</span>
        </div>
        <div class="px-6 pb-6 pt-6">
            <div class="grid grid-cols-[1fr_4.5rem_1fr] items-end gap-2 sm:grid-cols-[1fr_8rem_1fr]" {{ $d->f('from_place to_place') }}>
                <div class="min-w-0 text-left"><p class="text-xs font-semibold uppercase text-black/50">থেকে</p><p class="font-heading break-words text-2xl font-extrabold leading-tight sm:text-4xl">{{ $d->text('from_place', 'বাড়ি') }}</p></div>
                <svg viewBox="0 0 300 60" class="h-10 w-full overflow-visible text-[color:var(--t-primary)]" aria-hidden="true">
                    <path d="M 0 40 Q 150 -30 300 40" fill="none" stroke="currentColor" stroke-width="2" stroke-dasharray="6 6" opacity=".4"/>
                    <text class="t-plane" font-size="22" style="offset-rotate: auto">✈</text>
                </svg>
                <div class="min-w-0 text-right"><p class="text-xs font-semibold uppercase text-black/50">গন্তব্য</p><p class="font-heading break-words text-2xl font-extrabold leading-tight t-primary sm:text-4xl">{{ $d->text('to_place') }}</p></div>
            </div>
            <h1 class="font-heading mt-6 text-3xl font-extrabold leading-tight sm:text-4xl" data-split {{ $d->f('trip_title') }}>{{ $d->text('trip_title') }}</h1>
            <div class="mt-5 grid grid-cols-2 gap-4 text-left text-sm">
                <div {{ $d->f('travel_date') }}><p class="text-xs font-semibold uppercase text-black/50">তারিখ</p><p class="font-semibold">{{ $d->text('travel_date', '—') }}</p></div>
                <div {{ $d->f('travelers') }}><p class="text-xs font-semibold uppercase text-black/50">যাত্রী</p><p class="font-semibold">{{ $travelers ? implode(', ', $travelers) : '—' }}</p></div>
            </div>
        </div>
        <div class="t-pass__cut"></div>
        @if ($cover)
            <div class="p-3" {{ $d->f('cover_photo') }}><img src="{{ $cover['url'] }}" alt="" class="aspect-[16/10] w-full rounded-[1rem] object-cover" width="600" height="375"></div>
        @endif
        <div class="flex items-center justify-between px-6 pb-5 pt-2">
            <span class="h-8 flex-1" style="background: repeating-linear-gradient(90deg, #111 0 2px, transparent 2px 5px, #111 5px 6px, transparent 6px 9px)"></span>
            <span class="t-stamp ml-4 text-[color:var(--t-primary)]">Boarded ✓</span>
        </div>
    </div>
</header>

<main class="px-5 pb-20">
    @if ($d->has('intro'))<div class="t-reveal t-prose mx-auto max-w-2xl text-lg leading-loose" {{ $d->f('intro') }}>{{ $d->para('intro') }}</div>@endif

    @if ($d->has('stops'))
        <section class="relative mx-auto mt-20 max-w-3xl" data-draw-host {{ $d->f('stops') }}>
            <svg class="pointer-events-none absolute left-6 top-0 h-full w-4" viewBox="0 0 16 1000" preserveAspectRatio="none" aria-hidden="true">
                <path d="M8 0 V1000" fill="none" stroke="var(--t-primary)" stroke-width="3" stroke-dasharray="1" data-draw vector-effect="non-scaling-stroke"/>
            </svg>
            <ol class="space-y-10">
                @foreach ($d->items('stops') as $i => $s)
                    <li class="t-reveal relative pl-16">
                        <span class="absolute left-0 top-0 grid h-12 w-12 place-items-center rounded-full bg-accent text-xl shadow-md" aria-hidden="true">{{ $stamps[$i % count($stamps)] }}</span>
                        <div class="relative overflow-hidden rounded-[1.4rem] bg-white p-6 shadow-[0_20px_40px_-28px_rgba(0,0,0,.45)]">
                            <span class="t-stamp absolute right-3 top-3 rotate-12 text-xs text-[color:var(--t-accent)]">{{ $s['day'] ?? '' }}</span>
                            <h2 class="font-heading pr-16 text-3xl font-extrabold t-primary">{{ $s['place'] ?? '' }}</h2>
                            <p class="mt-2 text-lg text-soft">{{ $s['story'] ?? '' }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>
    @endif

    @if ($d->has('stats'))
        <section class="mx-auto mt-20 grid max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4" {{ $d->f('stats') }}>
            @foreach ($d->items('stats') as $i => $st)
                <div class="t-reveal rounded-[1.4rem] bg-primary px-4 py-6 text-center text-white" style="--i: {{ $i }}">
                    <p class="font-heading text-4xl font-extrabold t-accent">{{ $st['value'] ?? '' }}</p>
                    <p class="mt-1 text-sm opacity-85">{{ $st['label'] ?? '' }}</p>
                </div>
            @endforeach
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-24 max-w-5xl" {{ $d->f('gallery') }}>
            <p class="font-accent text-center text-4xl t-primary">স্ক্র্যাপবুক</p>
            <div class="mt-10">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'polaroid'])</div>
        </section>
    @endif

    @if ($d->has('closing_note'))
        <section class="t-reveal mx-auto mt-24 max-w-xl text-center" {{ $d->f('closing_note') }}>
            <span class="t-stamp text-lg t-primary">পরের গন্তব্য?</span>
            <div class="font-accent t-prose mt-6 text-3xl leading-snug">{{ $d->para('closing_note') }}</div>
        </section>
    @endif
</main>
@endsection
