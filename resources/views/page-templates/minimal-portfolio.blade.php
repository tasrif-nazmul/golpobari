@extends('page-templates._base')
@section('body')
@php $photo = $d->image('profile_photo'); @endphp
<main class="mx-auto max-w-3xl px-5 pb-16 pt-14 sm:px-8 sm:pt-24">
    <header class="t-reveal flex flex-col-reverse gap-8 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-heading text-[2.9rem] font-semibold leading-[1.05] tracking-tight sm:text-7xl" data-split {{ $d->f('full_name') }}>{{ $d->text('full_name') }}</h1>
            @if ($d->has('headline'))<p class="mt-5 max-w-xl text-xl text-soft sm:text-2xl" {{ $d->f('headline') }}>{{ $d->text('headline') }}</p>@endif
            @if ($d->has('location'))<p class="mt-4 text-sm font-medium uppercase tracking-[0.2em] t-accent" {{ $d->f('location') }}>{{ $d->text('location') }}</p>@endif
        </div>
        @if ($photo)<img src="{{ $photo['url'] }}" {{ $d->f('profile_photo') }} alt="{{ $d->text('full_name') }}" class="h-28 w-28 rounded-full object-cover grayscale sm:h-36 sm:w-36" width="144" height="144">@endif
    </header>

    <div class="mt-14 h-px w-16 bg-accent"></div>

    @php
        $sections = [
            ['about', 'About'], ['experience', 'Experience'], ['projects', 'Projects'], ['skills', 'Skills'], ['education', 'Education'],
        ];
    @endphp
    <div class="mt-10 space-y-14">
        @if ($d->has('about'))
            <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" {{ $d->f('about') }}>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">About</h2>
                <div class="t-prose text-lg">{{ $d->para('about') }}</div>
            </section>
        @endif
        @if ($d->has('experience'))
            <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" {{ $d->f('experience') }}>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">Experience</h2>
                <ol class="space-y-7">
                    @foreach ($d->items('experience') as $e)
                        <li>
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                                <h3 class="font-heading text-xl font-semibold">{{ $e['role'] ?? '' }}@if (! empty($e['org']))<span class="font-normal text-soft">, {{ $e['org'] }}</span>@endif</h3>
                                <span class="text-sm text-soft">{{ $e['period'] ?? '' }}</span>
                            </div>
                            @if (! empty($e['text']))<p class="mt-1 text-soft">{{ $e['text'] }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </section>
        @endif
        @if ($d->has('projects'))
            <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" {{ $d->f('projects') }}>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">Projects</h2>
                <ul class="divide-y divide-current/10 border-y border-current/10">
                    @foreach ($d->items('projects') as $p)
                        <li class="py-4">
                            @if ($d->link($p['link'] ?? null))
                                <a href="{{ $d->link($p['link']) }}" target="_blank" rel="noopener nofollow" class="group flex items-baseline justify-between gap-4 no-underline"><span class="font-heading text-xl font-semibold group-hover:t-accent">{{ $p['title'] ?? '' }}</span><span class="t-accent">↗</span></a>
                            @else
                                <span class="font-heading text-xl font-semibold">{{ $p['title'] ?? '' }}</span>
                            @endif
                            @if (! empty($p['text']))<p class="mt-1 text-soft">{{ $p['text'] }}</p>@endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
        @if ($d->has('skills'))
            <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" {{ $d->f('skills') }}>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">Skills</h2>
                <p class="text-lg">{{ collect($d->items('skills'))->pluck('name')->filter()->implode('  /  ') }}</p>
            </section>
        @endif
        @if ($d->has('education'))
            <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8" {{ $d->f('education') }}>
                <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">Education</h2>
                <ul class="space-y-3">
                    @foreach ($d->items('education') as $e)
                        <li class="flex flex-wrap justify-between gap-x-4"><span><b class="font-semibold">{{ $e['degree'] ?? '' }}</b> <span class="text-soft">{{ $e['school'] ?? '' }}</span></span><span class="text-sm text-soft">{{ $e['period'] ?? '' }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="t-reveal grid gap-3 sm:grid-cols-[9rem_1fr] sm:gap-8">
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-soft sm:pt-1.5">Contact</h2>
            <div class="space-y-4">
                @if ($d->has('email'))<a href="mailto:{{ $d->text('email') }}" class="block break-all font-heading text-2xl underline decoration-1 underline-offset-8 sm:text-3xl" style="text-decoration-color: var(--t-accent)">{{ $d->text('email') }}</a>@endif
                @if ($d->has('phone'))<a href="tel:{{ $d->tel($d->text('phone')) }}" class="block text-lg">{{ $d->text('phone') }}</a>@endif
                @include('page-templates.partials.socials')
            </div>
        </section>
    </div>
</main>
@endsection
