@extends('page-templates._base')
@section('body')
@php $photo = $d->image('photo'); $reasons = $d->items('reasons'); $n = count($reasons); $palette = ['var(--t-primary)', 'var(--t-accent)', '#E76F51', '#2A9D8F', '#8E7DBE']; @endphp
@include('page-templates.partials.gate', [
    'style' => 'envelope', 'icon' => \App\Support\Bn::digits($n).'টা ❤', 'mode' => 'hearts', 'initial' => '♥',
    'eyebrow' => $d->text('from_name') ? $d->text('from_name').' পাঠিয়েছে' : 'তোমার জন্য', 'title' => $d->text('to_name').', খামটা খোলো',
    'btn' => 'সিলমোহর ছুঁয়ে খোলো',
])
<header class="relative overflow-hidden px-5 pb-12 pt-16 text-center sm:pt-24">
    <p class="font-heading t-reveal text-[9rem] font-extrabold leading-none t-accent sm:text-[12rem]">{{ \App\Support\Bn::digits($n) }}</p>
    <h1 class="font-heading -mt-4 text-4xl font-extrabold leading-tight t-primary sm:text-6xl" data-split {{ $d->f('title to_name') }}>{{ $d->text('title', $d->text('to_name').', তোমাকে ভালোবাসার কারণগুলো') }}</h1>
    @if ($photo)
        <div class="t-reveal relative mx-auto mt-10 w-48" {{ $d->f('photo') }}>
            <img src="{{ $photo['url'] }}" alt="{{ $d->text('to_name') }}" class="t-blob aspect-square w-full object-cover shadow-xl" width="192" height="192">
        </div>
    @endif
    @if ($d->has('intro'))<div class="t-reveal t-prose mx-auto mt-8 max-w-lg text-lg text-soft" {{ $d->f('intro') }}>{{ $d->para('intro') }}</div>@endif
    <div class="mt-8 flex flex-wrap justify-center gap-3">
        <button type="button" class="t-btn t-btn--primary" data-shuffle="#cards">🎲 একটা র‍্যান্ডম কারণ</button>
    </div>
</header>

<main class="px-4 pb-20">
    <div id="cards" class="mx-auto grid max-w-5xl grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-5 lg:grid-cols-4" {{ $d->f('reasons') }}>
        @foreach ($reasons as $i => $r)
            <button type="button" class="t-flip t-reveal aspect-[3/4]" data-flip aria-pressed="false" aria-label="কারণ {{ $i + 1 }}" style="--i: {{ $i % 8 }}">
                <span class="t-flip__inner h-full">
                    <span class="t-flip__face relative grid h-full place-items-center overflow-hidden text-white shadow-[0_14px_30px_-16px_rgba(0,0,0,.5)]" style="background: {{ $palette[$i % count($palette)] }}">
                        <span class="pointer-events-none absolute inset-2 rounded-[.9rem] border-2 border-dashed border-white/40"></span>
                        <span><span class="font-heading block text-6xl font-extrabold">{{ \App\Support\Bn::digits($i + 1) }}</span><span class="text-xs font-semibold opacity-80">ট্যাপ করো</span></span>
                    </span>
                    <span class="t-flip__face t-flip__back grid h-full place-items-center bg-white p-4 text-[1.05rem] leading-snug shadow-[0_14px_30px_-16px_rgba(0,0,0,.4)]">
                        <span><span class="font-accent block text-2xl t-accent">#{{ \App\Support\Bn::digits($i + 1) }}</span>{{ $r['text'] ?? '' }}</span>
                    </span>
                </span>
            </button>
        @endforeach
    </div>

    @if ($d->has('closing_message'))
        <section class="t-reveal mx-auto mt-24 max-w-xl text-center" {{ $d->f('closing_message from_name') }}>
            <p class="text-5xl" aria-hidden="true">💛</p>
            <div class="t-prose font-heading mt-4 text-2xl leading-relaxed t-primary sm:text-3xl">{{ $d->para('closing_message') }}</div>
            @if ($d->has('from_name'))<p class="font-accent mt-6 text-3xl t-accent">— {{ $d->text('from_name') }}</p>@endif
        </section>
    @endif
</main>
@endsection
