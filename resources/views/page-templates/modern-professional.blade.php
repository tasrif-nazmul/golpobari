@extends('page-templates._base')
@section('body')
@php $photo = $d->image('profile_photo'); @endphp
<div class="lg:grid lg:min-h-dvh lg:grid-cols-[22rem_1fr]">
    <aside class="bg-primary px-6 pb-10 pt-12 text-white lg:sticky lg:top-0 lg:h-dvh lg:overflow-y-auto lg:px-9 lg:pt-16">
        @if ($photo)
            <img src="{{ $photo['url'] }}" alt="{{ $d->text('full_name') }}" class="h-32 w-32 rounded-2xl object-cover ring-4 ring-[var(--t-accent)] lg:h-40 lg:w-40" width="160" height="160">
        @endif
        <h1 class="font-heading mt-6 text-4xl font-extrabold leading-tight">{{ $d->text('full_name') }}</h1>
        <p class="mt-2 text-lg font-semibold t-accent">{{ $d->text('job_title') }}</p>
        @if ($d->has('location'))<p class="mt-1 text-white/60">{{ $d->text('location') }}</p>@endif

        <div class="mt-8 space-y-3 text-[0.95rem]">
            @if ($d->has('email'))<a class="flex items-center gap-3 break-all text-white/85 no-underline hover:text-white" href="mailto:{{ $d->text('email') }}"><span class="t-accent">✉</span>{{ $d->text('email') }}</a>@endif
            @if ($d->has('phone'))<a class="flex items-center gap-3 text-white/85 no-underline hover:text-white" href="tel:{{ $d->tel($d->text('phone')) }}"><span class="t-accent">☎</span>{{ $d->text('phone') }}</a>@endif
        </div>
        @if ($d->link($d->text('cv_link')))
            <a href="{{ $d->link($d->text('cv_link')) }}" target="_blank" rel="noopener nofollow" class="t-btn t-btn--accent mt-7 w-full">Download CV</a>
        @endif
        @if ($d->has('skills'))
            <h2 class="mt-10 text-xs font-bold uppercase tracking-[0.2em] text-white/50">Skills</h2>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach ($d->items('skills') as $s)<li class="rounded-md bg-white/10 px-2.5 py-1 text-sm">{{ $s['name'] ?? '' }}</li>@endforeach
            </ul>
        @endif
        <div class="mt-8">@include('page-templates.partials.socials')</div>
    </aside>

    <main class="px-6 py-12 sm:px-10 lg:px-16 lg:py-20">
        <section class="t-reveal max-w-3xl">
            <h2 class="text-xs font-bold uppercase tracking-[0.2em] t-primary opacity-60">Profile</h2>
            <div class="t-prose mt-3 text-xl leading-relaxed">{{ $d->para('summary') }}</div>
        </section>

        @if ($d->has('stats'))
            <section class="mt-12 grid max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($d->items('stats') as $i => $s)
                    <div class="t-reveal rounded-2xl bg-white p-5 shadow-sm" style="--i: {{ $i }}">
                        <p class="font-heading text-4xl font-extrabold t-accent">{{ $s['value'] ?? '' }}</p>
                        <p class="mt-1 text-sm text-soft">{{ $s['label'] ?? '' }}</p>
                    </div>
                @endforeach
            </section>
        @endif

        @if ($d->has('experience'))
            <section class="mt-14 max-w-3xl">
                <h2 class="font-heading text-2xl font-extrabold t-primary">Experience</h2>
                <ol class="relative mt-6 space-y-8 border-l-2 pl-7" style="border-color: color-mix(in oklab, var(--t-accent) 50%, transparent)">
                    @foreach ($d->items('experience') as $e)
                        <li class="t-reveal relative">
                            <span class="absolute -left-[2.15rem] top-1.5 h-3.5 w-3.5 rounded-full bg-accent ring-4 ring-[var(--t-bg)]"></span>
                            <p class="text-sm font-semibold t-accent">{{ $e['period'] ?? '' }}</p>
                            <h3 class="font-heading mt-1 text-xl font-bold">{{ $e['role'] ?? '' }}</h3>
                            <p class="font-semibold text-soft">{{ $e['org'] ?? '' }}</p>
                            @if (! empty($e['text']))<p class="mt-2 text-soft">{!! \App\Support\TemplateData::lines($e['text']) !!}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($d->has('education'))
            <section class="mt-14 max-w-3xl">
                <h2 class="font-heading text-2xl font-extrabold t-primary">Education</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2">
                    @foreach ($d->items('education') as $e)
                        <div class="t-reveal rounded-2xl bg-white p-5 shadow-sm"><p class="font-bold">{{ $e['degree'] ?? '' }}</p><p class="text-soft">{{ $e['school'] ?? '' }}</p><p class="text-sm t-accent">{{ $e['period'] ?? '' }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
