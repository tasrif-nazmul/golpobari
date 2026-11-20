@extends('page-templates._base')
@section('body')
@php $photo = $d->image('photo'); $wa = $d->whatsapp($d->text('whatsapp')); $cta = $d->text('cta_text', "Let's work together"); @endphp
<div class="pointer-events-none fixed inset-0 -z-0 opacity-40" aria-hidden="true" style="background: radial-gradient(40rem 30rem at 85% -10%, color-mix(in oklab, var(--t-accent) 55%, transparent), transparent), radial-gradient(30rem 25rem at -10% 30%, color-mix(in oklab, var(--t-primary) 40%, transparent), transparent)"></div>
<div class="relative">
    <header class="mx-auto flex max-w-6xl items-center justify-between px-5 py-6">
        <span class="font-heading text-lg font-bold">{{ $d->text('brand_name') }}</span>
        @if ($d->has('email'))<a href="mailto:{{ $d->text('email') }}" class="text-sm font-semibold opacity-80 no-underline hover:opacity-100">{{ $d->text('email') }}</a>@endif
    </header>

    <section class="mx-auto grid max-w-6xl items-center gap-12 px-5 pb-20 pt-10 md:grid-cols-[1.4fr_1fr] md:pt-20">
        <div>
            <h1 class="font-heading t-reveal text-[2.75rem] font-bold leading-[1.05] tracking-tight sm:text-7xl">
                <span style="background: linear-gradient(90deg, var(--t-primary), var(--t-accent)); -webkit-background-clip: text; background-clip: text; color: transparent">{{ $d->text('headline') }}</span>
            </h1>
            @if ($d->has('intro'))<p class="t-reveal mt-6 max-w-xl text-lg opacity-75" {{ $d->f('intro') }}>{{ $d->text('intro') }}</p>@endif
            <div class="t-reveal mt-8 flex flex-wrap gap-3">
                <a href="{{ $wa ?: ($d->has('email') ? 'mailto:'.$d->text('email') : '#services') }}" @if ($wa) target="_blank" rel="noopener" @endif class="t-btn text-white" style="background: linear-gradient(90deg, var(--t-primary), var(--t-accent))">{{ $cta }} →</a>
                <a href="#services" class="t-btn t-btn--ghost">Services</a>
            </div>
        </div>
        @if ($photo)
            <div class="t-reveal relative mx-auto w-64 sm:w-80">
                <div class="absolute -inset-1.5 rounded-[2rem] opacity-80 blur-md" style="background: linear-gradient(135deg, var(--t-primary), var(--t-accent))"></div>
                <img src="{{ $photo['url'] }}" {{ $d->f('photo') }} alt="{{ $d->text('brand_name') }}" class="relative aspect-[4/5] w-full rounded-[2rem] object-cover" width="320" height="400">
            </div>
        @endif
    </section>

    <main class="mx-auto max-w-6xl px-5 pb-16">
        <section id="services" class="scroll-mt-6">
            <p class="text-sm font-semibold uppercase tracking-[0.25em] t-primary">Services</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($d->items('services') as $i => $s)
                    <article class="t-reveal rounded-3xl bg-white/[0.04] p-6 ring-1 ring-white/10 transition hover:ring-[var(--t-accent)]" style="--i: {{ $i }}">
                        <p class="font-heading text-sm opacity-50">0{{ $i + 1 }}</p>
                        <h3 class="font-heading mt-3 text-2xl font-bold">{{ $s['title'] ?? '' }}</h3>
                        <p class="mt-2 opacity-70">{{ $s['text'] ?? '' }}</p>
                        @if (! empty($s['price']))<p class="mt-5 font-semibold t-primary">{{ $s['price'] }}</p>@endif
                    </article>
                @endforeach
            </div>
        </section>

        @if ($d->has('process'))
            <section class="mt-24" {{ $d->f('process') }}>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] t-accent">Process</p>
                <ol class="mt-6 grid gap-6 {{ ['md:grid-cols-1', 'md:grid-cols-2', 'md:grid-cols-3', 'md:grid-cols-4'][min(count($d->items('process')), 4) - 1] }}">
                    @foreach ($d->items('process') as $i => $p)
                        <li class="t-reveal border-t-2 pt-5" style="border-color: var(--t-accent); --i: {{ $i }}">
                            <p class="font-heading text-5xl font-bold opacity-20">{{ $i + 1 }}</p>
                            <h3 class="font-heading mt-2 text-xl font-bold">{{ $p['title'] ?? '' }}</h3>
                            <p class="mt-1 opacity-70">{{ $p['text'] ?? '' }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($d->has('testimonials'))
            <section class="mt-24 grid gap-5 md:grid-cols-2" {{ $d->f('testimonials') }}>
                @foreach ($d->items('testimonials') as $t)
                    <figure class="t-reveal rounded-3xl bg-white/[0.04] p-7 ring-1 ring-white/10">
                        <blockquote class="text-xl leading-relaxed">“{{ $t['text'] ?? '' }}”</blockquote>
                        <figcaption class="mt-4 font-semibold t-primary">{{ $t['name'] ?? '' }}</figcaption>
                    </figure>
                @endforeach
            </section>
        @endif

        <section class="t-reveal mt-24 rounded-[2rem] p-8 text-center sm:p-14" style="background: linear-gradient(135deg, var(--t-primary), var(--t-accent))">
            <h2 class="font-heading text-4xl font-bold text-white sm:text-5xl">{{ $cta }}</h2>
            <div class="mt-7 flex flex-wrap justify-center gap-3">
                @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="t-btn bg-white text-black">WhatsApp</a>@endif
                @if ($d->has('email'))<a href="mailto:{{ $d->text('email') }}" class="t-btn t-btn--ghost text-white">Email</a>@endif
                @if ($d->has('phone'))<a href="tel:{{ $d->tel($d->text('phone')) }}" class="t-btn t-btn--ghost text-white">{{ $d->text('phone') }}</a>@endif
            </div>
            <div class="mt-6 flex justify-center text-white">@include('page-templates.partials.socials')</div>
        </section>
    </main>
</div>
@endsection
