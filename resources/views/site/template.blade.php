@extends('layouts.site')
@php
    $payload = $template->previewPayload();
    $guide = \App\Support\TemplateRegistry::guide($template->layout, $template->fields->map->toSchema()->all(), $template->sampleContent());
@endphp
@section('content')
<section class="px-4 pb-10 pt-4 sm:px-6 md:pt-8">
    <div class="container-x">
        <a href="{{ route('templates.index', ['category' => $template->category->slug]) }}" class="text-sm font-semibold text-ink-mute hover:text-ink">← {{ $template->category->displayName() }}</a>
        <div class="mt-5 grid items-start gap-10 md:grid-cols-[auto_1fr] md:gap-14">
            {{-- The real page, live, in a phone. Tap it, scroll it, hear it. --}}
            <div class="relative mx-auto w-full max-w-[24rem] md:sticky md:top-24 md:w-[23rem]">
                <div aria-hidden="true" class="absolute -inset-6 -z-10 rounded-[3rem] opacity-60 blur-2xl" style="background: radial-gradient(closest-side, color-mix(in oklab, {{ $template->category->accent_color ?: '#e3ad4a' }} 55%, transparent), transparent)"></div>
                <div class="phone" style="height: min(780px, 78dvh); aspect-ratio: auto">
                    <div class="phone__screen">
                        <iframe src="{{ route('templates.render', $template) }}" title="{{ $template->name }} লাইভ প্রিভিউ" allow="autoplay" class="h-full w-full"></iframe>
                    </div>
                </div>
                <p class="mt-4 text-center text-sm text-ink-mute">👆 ছুঁয়ে, স্ক্রল করে দেখুন। এটাই আসল পেজ, শুধু লেখা আর ছবি নমুনা।</p>
            </div>

            <div>
                <p class="text-sm font-semibold text-ink-mute">{{ $payload['category'] }}</p>
                <h1 class="font-fancy mt-1 text-[2.8rem] leading-tight sm:text-6xl">{{ $template->name }}</h1>
                <p class="mt-3 max-w-xl text-lg leading-relaxed text-ink-soft">{{ $template->description }}</p>

                <div class="mt-6 flex flex-wrap gap-2">
                    @if ($payload['music'])<span class="chip !bg-night !text-paper"><span class="flex h-3 items-end gap-[2px] text-holud"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span> গান: {{ $payload['music'] }}</span>@endif
                    <span class="chip">✨ অ্যানিমেশন</span>
                    <span class="chip">📱 মোবাইলে সুন্দর</span>
                    <span class="chip">🔗 নিজের লিংক</span>
                </div>

                <div x-data class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ $payload['order'] }}" class="btn btn-primary text-lg">এই টেমপ্লেট দিয়ে বানাই <x-icon name="arrow-right" class="h-5 w-5" /></a>
                    <button type="button" class="btn btn-ghost text-lg md:hidden" @click="$dispatch('tpl-preview', @js($payload))">পুরো স্ক্রিনে দেখুন</button>
                </div>

                <div class="mt-12">
                    <h2 class="text-2xl font-bold">পেজটা উপর থেকে নিচে যেমন</h2>
                    <p class="mt-1 text-ink-soft">অর্ডারের সময় ঠিক এই অংশগুলোর জন্যই লেখা আর ছবি চাওয়া হবে।</p>
                    <ol class="mt-6 space-y-3">
                        @foreach ($guide['sections'] as $sec)
                            <li class="flex gap-4 rounded-2xl bg-white p-4 shadow-[var(--shadow-soft)]">
                                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-paper-2 text-xl">{{ $sec['icon'] }}</span>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-sindoor">অংশ {{ \App\Support\Bn::digits($sec['n']) }}</p>
                                    <h3 class="font-sans text-lg font-semibold leading-snug">{{ $sec['title'] }}</h3>
                                    <p class="text-sm leading-snug text-ink-soft">{{ $sec['where'] }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($related->isNotEmpty())
<section class="section pt-10">
    <div class="container-x">
        <x-section-head title="একই ধরনের আরও টেমপ্লেট" />
        <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($related as $t)<x-template-card :template="$t" :compact="true" />@endforeach
        </div>
    </div>
</section>
@endif
@endsection

@push('sticky')
<div class="sticky bottom-0 z-40 border-t border-ink/10 bg-paper/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur md:hidden">
    <a href="{{ $payload['order'] }}" class="btn btn-primary w-full">এই টেমপ্লেট দিয়ে বানাই <x-icon name="arrow-right" class="h-5 w-5" /></a>
</div>
@endpush
