@extends('page-templates._base')
@section('body')
@php
    $fest = $d->text('festival', 'ঈদ মোবারক');
    $kind = match (true) {
        str_contains($fest, 'ঈদ') => 'eid',
        str_contains($fest, 'নববর্ষ') => 'boishakh',
        str_contains($fest, 'শারদীয়') || str_contains($fest, 'দীপাবলি') => 'puja',
        str_contains($fest, 'বড়দিন') => 'xmas',
        default => 'newyear',
    };
    $photo = $d->image('photo');
    $icon = ['eid' => '🌙', 'boishakh' => '🪘', 'puja' => '🪔', 'xmas' => '🎄', 'newyear' => '🎆'][$kind];
@endphp
@include('page-templates.partials.gate', ['icon' => $icon, 'mode' => 'confetti', 'eyebrow' => $d->text('to_name') ?: 'আপনার জন্য', 'title' => $fest, 'sub' => $d->text('from_name').'-এর পক্ষ থেকে', 'btn' => 'শুভেচ্ছা খুলুন'])

<header class="relative overflow-hidden px-5 pb-16 pt-28 text-center {{ $kind === 'boishakh' ? 't-dhol-bg' : '' }}" data-confetti="#e8b931,#ffffff,#d1495b,#66a182">
    @if ($kind === 'eid')
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">@for ($i = 0; $i < 30; $i++)<span class="t-star" style="left: {{ ($i * 47) % 100 }}%; top: {{ ($i * 31 + 5) % 70 }}%; animation-delay: -{{ $i % 5 }}s"></span>@endfor</div>
        @foreach ([8, 22, 74, 90] as $i => $x)<span class="t-lantern" style="left: {{ $x }}%; --i: {{ $i }}; --len: {{ [3, 6, 4.5, 2.5][$i] }}rem" aria-hidden="true"><b></b></span>@endforeach
        <div class="t-moon t-reveal mx-auto" aria-hidden="true"></div>
    @elseif ($kind === 'boishakh')
        <div class="t-reveal relative mx-auto h-44 w-44 t-alpona--spin" aria-hidden="true">@include('page-templates.partials.alpona', ['rings' => 3, 'petals' => 10, 'class' => 'h-full w-full'])</div>
        @include('page-templates.partials.particles', ['chars' => ['❀', '✿'], 'count' => 14, 'class' => 't-accent'])
    @elseif ($kind === 'puja')
        @include('page-templates.partials.particles', ['chars' => ['✦', '·', '✧'], 'count' => 20, 'rise' => true, 'class' => 't-accent'])
        <div class="t-reveal flex justify-center" aria-hidden="true">@for ($i = 0; $i < 5; $i++)<span class="t-diya is-lit"><i></i></span>@endfor</div>
    @else
        @include('page-templates.partials.particles', ['chars' => ['❄', '✦', '✧'], 'count' => 22, 'class' => 'opacity-80'])
        <p class="t-reveal t-bob text-8xl" aria-hidden="true">{{ $icon }}</p>
    @endif

    <h1 class="font-heading relative mt-10 text-6xl leading-tight t-accent sm:text-8xl" data-split {{ $d->f('festival') }}>{{ $fest }}</h1>
    @if ($d->has('greeting_title'))<p class="font-accent t-reveal relative mx-auto mt-4 max-w-lg text-2xl sm:text-3xl" {{ $d->f('greeting_title') }}>{{ $d->text('greeting_title') }}</p>@endif
    @if ($d->has('to_name'))<p class="t-reveal relative mt-6 inline-block rounded-full border px-5 py-1.5 text-sm" style="border-color: var(--t-accent)" {{ $d->f('to_name') }}>প্রতি: {{ $d->text('to_name') }}</p>@endif
</header>

<main class="px-5 pb-20">
    <section class="t-reveal t-nakshi relative mx-auto max-w-2xl rounded-[2rem] px-7 py-14 text-center sm:px-14" style="background: color-mix(in oklab, var(--t-primary) 70%, transparent)" {{ $d->f('message photo') }}>
        @if ($photo)<img src="{{ $photo['url'] }}" alt="" class="t-arch mx-auto mb-10 aspect-[4/5] w-52 object-cover shadow-2xl" width="208" height="260">@endif
        <div class="t-prose text-xl leading-loose">{{ $d->para('message') }}</div>
        <p class="font-heading mt-10 text-4xl t-accent" {{ $d->f('from_name') }}>— {{ $d->text('from_name') }}</p>
        @if ($d->has('family'))<p class="mt-3 opacity-75" {{ $d->f('family') }}>{{ implode(' · ', $d->list('family')) }}</p>@endif
    </section>

    @if ($d->has('salami_number'))
        <section class="t-reveal mx-auto mt-16 max-w-sm rounded-[1.8rem] bg-accent px-6 py-8 text-center text-[#1a1a1a] shadow-2xl" {{ $d->f('salami_number') }}>
            <p class="text-5xl" aria-hidden="true">🧧</p>
            <p class="font-heading mt-3 text-3xl">সালামি কর্নার</p>
            <p class="mt-1 text-sm opacity-80">দোয়া তো দিলাম, এবার সালামিটা? 😄</p>
            <p class="mt-4 font-mono text-2xl font-bold tracking-wider">{{ $d->text('salami_number') }}</p>
            <p class="text-xs opacity-70">bKash</p>
            <button type="button" class="t-btn mt-4 bg-[#1a1a1a] text-white" data-copy="{{ $d->tel($d->text('salami_number')) }}">নম্বর কপি করুন</button>
        </section>
    @endif

    <div class="mt-16 text-center"><button type="button" class="t-btn t-btn--accent" data-burst="{{ $icon }}">{{ $icon }} শুভেচ্ছা ফেরত পাঠাও</button></div>
</main>
@endsection
