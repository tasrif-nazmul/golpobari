@extends('page-templates._base')
@section('body')
@php $hero = $d->image('hero_photo'); $date = $d->date('anniversary_date'); @endphp
@include('page-templates.partials.gate', [
    'style' => 'book', 'icon' => '💍', 'mode' => 'confetti', 'eyebrow' => 'শুভ বিবাহবার্ষিকী', 'ribbon' => '#c8a45d', 'flowerColor' => '#f3ebdd',
    'title' => $d->text('partner_one').' ও '.$d->text('partner_two'),
    'sub' => 'একসাথে পথচলার গল্প',
    'btn' => 'ফিতে খুলে গল্পটা পড়ুন', 'bg' => '#16302a',
])
<header class="relative overflow-hidden px-5 pb-20 pt-16 text-center sm:pt-24" data-confetti="#c8a45d,#f3ebdd,#123b34">
    @include('page-templates.partials.particles', ['chars' => ['✦', '·', '✧'], 'count' => 18, 'class' => 't-accent'])
    <p class="t-eyebrow t-eyebrow--wide t-reveal t-accent">Happy Anniversary</p>
    <h1 class="font-heading mt-4 text-6xl leading-none sm:text-8xl" data-split {{ $d->f('partner_one partner_two') }}>{{ $d->text('partner_one') }} <span class="t-accent italic">&amp;</span> {{ $d->text('partner_two') }}</h1>

    @if ($hero)
        <div class="t-reveal relative mx-auto mt-16 w-64 sm:w-80" {{ $d->f('hero_photo') }}>
            <div class="absolute -inset-12 t-alpona--spin opacity-40">@include('page-templates.partials.alpona', ['rings' => 4, 'petals' => 8, 'stroke' => 1, 'class' => 'h-full w-full'])</div>
            <div class="t-arch relative aspect-[3/4] border-[3px] p-2" style="border-color: var(--t-accent)">
                <div class="t-arch h-full w-full"><img src="{{ $hero['url'] }}" alt="" class="h-full w-full object-cover" width="320" height="426"></div>
            </div>
        </div>
    @endif

    @if ($date)
        <div class="t-reveal mx-auto mt-24 max-w-md" {{ $d->f('anniversary_date') }}>
            <p class="font-heading text-2xl italic t-accent">একসাথে</p>
            <div class="mt-3 grid grid-cols-3 divide-x" style="border-color: color-mix(in oklab, var(--t-accent) 40%, transparent)" data-since="{{ $date->toDateString() }}" data-unit="ymd">
                @foreach (['y' => 'বছর', 'm' => 'মাস', 'd' => 'দিন'] as $k => $l)
                    <div class="px-2" style="border-color: color-mix(in oklab, var(--t-accent) 40%, transparent)"><p class="font-heading text-6xl t-accent" data-ymd="{{ $k }}">০</p><p class="text-sm opacity-70">{{ $l }}</p></div>
                @endforeach
            </div>
            <p class="mt-4 text-sm opacity-70">{{ \App\Support\Bn::date($date) }} থেকে</p>
        </div>
    @endif

    @if ($d->has('intro'))<div class="t-reveal t-prose mx-auto mt-12 max-w-xl text-xl leading-loose opacity-90" {{ $d->f('intro') }}>{{ $d->para('intro') }}</div>@endif
</header>

<main class="px-5 pb-20">
    @if ($d->has('milestones'))
        <section class="mx-auto max-w-3xl" {{ $d->f('milestones') }}>
            <div class="t-divider"><span class="font-heading text-2xl">❦</span></div>
            <div class="mt-12 space-y-8">
                @foreach ($d->items('milestones') as $i => $m)
                    <article class="t-reveal relative overflow-hidden rounded-[1.6rem] border px-7 py-8 sm:px-10" style="border-color: color-mix(in oklab, var(--t-accent) 35%, transparent); background: color-mix(in oklab, var(--t-primary) 60%, transparent)">
                        <span class="font-heading pointer-events-none absolute -right-2 -top-8 text-[8rem] leading-none opacity-10 t-accent">{{ \App\Support\Bn::digits($i + 1) }}</span>
                        <p class="t-eyebrow t-accent">{{ $m['date'] ?? '' }}</p>
                        <h2 class="font-heading mt-2 text-3xl sm:text-4xl">{{ $m['title'] ?? '' }}</h2>
                        <p class="mt-3 text-lg leading-relaxed opacity-85">{{ $m['text'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    @if ($d->gallery('gallery'))
        <section class="mx-auto mt-24 max-w-5xl" {{ $d->f('gallery') }}>
            <p class="font-heading text-center text-4xl italic t-accent">স্মৃতির ফ্রেম</p>
            <div class="mt-10">@include('page-templates.partials.gallery', ['items' => $d->gallery('gallery'), 'style' => 'prints'])</div>
        </section>
    @endif

    <section class="t-reveal t-nakshi relative mx-auto mt-24 max-w-2xl rounded-[2rem] px-8 py-14 text-center" style="background: color-mix(in oklab, var(--t-accent) 12%, transparent)" {{ $d->f('message') }}>
        @include('page-templates.partials.alpona', ['rings' => 1, 'petals' => 12, 'class' => 'mx-auto h-16 w-16'])
        <div class="t-prose font-heading mt-6 text-2xl leading-relaxed sm:text-3xl">{{ $d->para('message') }}</div>
        <button type="button" class="t-btn t-btn--accent mt-10" data-burst="💛">💛 ভালোবাসা</button>
    </section>
</main>
@endsection
