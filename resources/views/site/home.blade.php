@extends('layouts.site')

@php
    use App\Support\Bn;
    $heroTitle = $settings->get('hero_title');
    // Hand-underline the word "ঠিকানা" (or the last word) of the admin-editable headline.
    $word = str_contains($heroTitle, 'ঠিকানা') ? 'ঠিকানা' : (preg_split('/\s+/u', trim($heroTitle, " ।.!")) ?: [''])[count(preg_split('/\s+/u', trim($heroTitle, " ।.!")) ?: ['']) - 1];
    [$before, $after] = array_pad(explode($word, $heroTitle, 2), 2, '');
@endphp

@section('content')
{{-- ============ HERO: woven cotton, a stitched lotus and one live phone ============ --}}
<section class="cloth relative overflow-hidden border-b border-ink/10">
    <x-kantha type="corner" class="pointer-events-none absolute left-3 top-3 h-20 w-20 text-sindoor/50 [--k2:rgb(31_43_77/.4)] sm:h-28 sm:w-28" />
    <x-kantha type="corner" class="pointer-events-none absolute bottom-3 right-3 h-20 w-20 rotate-180 text-sindoor/50 [--k2:rgb(31_43_77/.4)] sm:h-28 sm:w-28" />
    <div class="container-x relative grid items-center gap-12 px-5 pb-16 pt-12 sm:px-8 md:grid-cols-[1.1fr_.9fr] md:gap-8 md:pb-24 md:pt-20">
        <div class="text-center md:text-left">
            <p class="kicker reveal">ভালোবাসা, জন্মদিন, বিয়ে, ঈদ আর স্মৃতির জন্য</p>
            <h1 class="reveal mt-4 text-[2.6rem] leading-[1.3] sm:text-[3.6rem] lg:text-[4.1rem]">
                {{ $before }}<span class="hand-underline text-sindoor">{{ $word }}<svg viewBox="0 0 200 20" preserveAspectRatio="none" aria-hidden="true"><path d="M3 12C50 6 120 6 197 11" fill="none" stroke="#e2aa3c" stroke-width="4" stroke-linecap="round" stroke-dasharray="9 7"/></svg></span>{{ $after }}
            </h1>
            <p class="reveal mx-auto mt-5 max-w-xl text-lg leading-relaxed text-ink-soft sm:text-xl md:mx-0">{{ $settings->get('hero_subtitle') }}</p>
            <div class="reveal mt-8 flex flex-col items-center gap-4 sm:flex-row md:items-start">
                <a href="{{ route('order.create') }}" class="btn btn-primary text-lg">{{ $settings->get('hero_cta_primary') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
                <a href="{{ route('templates.index') }}" class="font-semibold text-ink underline decoration-sindoor/50 decoration-dashed decoration-2 underline-offset-8 hover:decoration-sindoor">{{ $settings->get('hero_cta_secondary') }}</a>
            </div>
        </div>

        @if ($heroItems->isNotEmpty())
        <div class="reveal relative mx-auto w-full max-w-[22rem]" x-data="heroPhones(@js($heroItems))">
            <x-kantha type="lotus" class="pointer-events-none absolute left-1/2 top-1/2 h-[30rem] w-[30rem] -translate-x-1/2 -translate-y-1/2 text-sindoor/35 [--k2:rgb(31_43_77/.3)]" />
            <button type="button" class="phone relative mx-auto block w-[68%] text-left" style="aspect-ratio: 390/800" @click="$dispatch('tpl-preview', cur.payload)" :aria-label="(cur.name || '') + ' লাইভ প্রিভিউ'">
                <div class="phone__screen">
                    <template x-for="(it, k) in items" :key="k"><img :src="it.cover" alt="" class="absolute inset-0 transition-opacity duration-700" :class="k === i ? 'opacity-100' : 'opacity-0'"></template>
                </div>
            </button>
            <p class="relative mt-5 text-center text-ink-soft">
                <span x-text="cur.name"></span> ·
                <button type="button" class="font-semibold text-sindoor underline decoration-dashed underline-offset-4" @click="$dispatch('tpl-preview', cur.payload)">খুলে দেখুন</button>
            </p>
        </div>
        @endif
    </div>
</section>

{{-- ============ TEMPLATES ============ --}}
@if ($settings->bool('show_templates') && $templates->isNotEmpty())
<section class="section" id="templates">
    <div class="container-x">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="frame-title text-[1.9rem] sm:text-[2.6rem]">আমাদের নকশা</h2>
            <p class="mt-5 text-lg text-ink-soft">ছবিতে ছুঁয়ে খুলে দেখুন। খাম খোলে, পাতা ওল্টায়, গান বাজে, ঠিক যেমনটা প্রিয় মানুষ দেখবে।</p>
        </div>
        <div class="mt-12 grid gap-7 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($templates as $template)
                <x-template-card :template="$template" class="reveal" />
            @endforeach
        </div>
        @if ($categories->isNotEmpty())
            <p class="mt-12 text-center leading-loose text-ink-soft">
                <span class="font-semibold text-ink">উপলক্ষ ধরে খুঁজুন:</span>
                @foreach ($categories as $c)
                    <a href="{{ route('categories.show', $c) }}" class="mx-1.5 whitespace-nowrap underline decoration-sindoor/40 decoration-dashed underline-offset-4 hover:text-sindoor">{{ $c->displayName() }}</a>@if (! $loop->last)<span aria-hidden="true" class="text-holud">✦</span>@endif
                @endforeach
            </p>
        @endif
        <div class="mt-8 text-center">
            <a href="{{ route('templates.index') }}" class="btn btn-ghost">সব নকশা দেখুন <x-icon name="arrow-right" class="h-5 w-5" /></a>
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS: three stops along one thread ============ --}}
<section class="section cloth border-y border-ink/10" id="how">
    <div class="container-x">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="frame-title text-[1.9rem] sm:text-[2.6rem]">যেভাবে বানাবেন</h2>
            <p class="mt-5 text-lg text-ink-soft">পুরোটা মোবাইল থেকেই হয়ে যায়, মিনিট দশেকে।</p>
        </div>
        @php
            $steps = [
                ['১', 'নকশা বাছুন', 'উপলক্ষ মিলিয়ে একটা নকশা বেছে নিন। খুলে, শুনে, স্ক্রল করে দেখে নিন।'],
                ['২', 'লেখা, ছবি আর গান দিন', 'প্রতিটা ঘরের পাশেই দেখাবে লেখাটা পেজের কোথায় বসবে। তারপর bKash-এ পেমেন্ট।'],
                ['৩', 'লিংকটা পাঠিয়ে দিন', 'আমরা পেজ সাজিয়ে লাইভ করে দিই। আপনি পাবেন নিজের লিংক, প্রিয় মানুষ পাবে একটা খাম।'],
            ];
        @endphp
        <ol class="relative mt-14 grid gap-10 md:grid-cols-3 md:gap-8">
            <li aria-hidden="true" class="absolute left-[16%] right-[16%] top-8 hidden border-t-2 border-dashed border-sindoor/40 md:block"></li>
            @foreach ($steps as [$n, $title, $text])
                <li class="reveal relative text-center">
                    <span class="relative mx-auto grid h-16 w-16 place-items-center rounded-full bg-paper font-display text-3xl text-sindoor border-2 border-dashed border-sindoor/50" style="box-shadow: 0 0 0 6px var(--color-paper), 0 0 0 7.5px rgb(31 43 77 / .25)">{{ $n }}</span>
                    <h3 class="mt-5 text-[1.45rem]">{{ $title }}</h3>
                    <p class="mx-auto mt-2 max-w-xs text-ink-soft">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-12 text-center"><a href="{{ route('order.create') }}" class="btn btn-primary">শুরু করি <x-icon name="arrow-right" class="h-5 w-5" /></a></div>
    </div>
</section>

{{-- ============ MUSIC ============ --}}
<section class="section">
    <div class="container-x grid items-center gap-10 md:grid-cols-[1fr_1.1fr]">
        <div class="text-center md:text-left">
            <h2 class="text-[2rem] leading-snug sm:text-[2.6rem]">পেজ খুললেই বাজবে <span class="text-sindoor">একটা গান</span></h2>
            <p class="mt-4 text-lg leading-relaxed text-ink-soft">আমাদের নিজেদের বানানো ছয়টা সুর আছে, শুনে দেখুন। চাইলে নিজের পছন্দের গানটাও দিয়ে দিতে পারেন। পেজের কোণে থাকবে চালু-বন্ধের ছোট্ট বোতাম।</p>
        </div>
        <ul class="night kantha-border grid gap-1 rounded-[1.6rem] p-6 [--kb:var(--color-holud)] [--kb2:#fff] sm:grid-cols-2 sm:p-8" x-data="trackList">
            @foreach ($tracks as $t)
                <li class="relative z-[1]">
                    <button type="button" @click="toggle(@js($t['key']), @js($t['url']))" class="flex w-full items-center gap-3 rounded-xl p-2.5 text-left transition hover:bg-white/[0.06]">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full ring-1 transition" :class="playing === @js($t['key']) ? 'bg-holud text-ink ring-holud' : 'text-holud ring-holud/50'">
                            <svg x-show="playing !== @js($t['key'])" class="ml-0.5 h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg>
                            <span x-show="playing === @js($t['key'])" class="flex h-4 items-end gap-[3px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span>
                        </span>
                        <span class="min-w-0 leading-tight"><b class="block font-display text-[1.1rem] font-normal text-paper">{{ $t['title'] }}</b><span class="block truncate text-sm text-paper/60">{{ $t['mood'] }}</span></span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ============ ABOUT: a letter addressed to a web address ============ --}}
<section class="section cloth border-y border-ink/10" id="about">
    <div class="container-x grid items-center gap-12 md:grid-cols-2">
        <div class="reveal text-center md:text-left">
            <h2 class="text-[1.9rem] leading-snug sm:text-[2.5rem]">{{ $settings->get('about_title') }}</h2>
            <p class="mt-5 text-lg leading-relaxed text-ink-soft sm:text-xl">{{ $settings->get('about_text') }}</p>
            <p class="mt-5 text-ink-soft">যেকোনো ফোনে খোলে, Messenger-এ সুন্দর প্রিভিউ দেখায়, আর চাইলে পাসওয়ার্ড দিয়ে প্রাইভেট রাখা যায়। ডেলিভারি {{ $settings->get('delivery_time') }}র মধ্যে।</p>
        </div>
        <div class="reveal relative mx-auto w-full max-w-md">
            <div class="relative rotate-[-1.5deg] rounded-md bg-[#fffaf1] p-6 shadow-[var(--shadow-lift)] sm:p-8" style="background-image: var(--grain-img)">
                <div class="absolute right-5 top-5 grid h-16 w-14 place-items-center rounded-sm border-2 border-dashed border-sindoor/50 bg-white p-1"><x-kantha type="lotus" class="h-full w-full text-sindoor [--k2:#1f2b4d]" /></div>
                <p class="font-hand text-2xl text-ink-mute">প্রাপক</p>
                <p class="mt-1 font-display text-2xl">আম্মু</p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 font-hand text-2xl text-ink-mute">ঠিকানা</p>
                <p class="mt-1 break-all font-serif text-xl text-sindoor sm:text-2xl">golpobari.com/<b>ammu</b></p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 text-ink-soft">ভেতরে: ৩২টা ছবি, ছোটবেলার গল্প, একটা প্রিয় গান আর একটা লম্বা ধন্যবাদ।</p>
                <span class="absolute -bottom-6 -left-5 grid h-16 w-16 rotate-[-12deg] place-items-center rounded-full font-brand text-2xl text-white shadow-lg" style="background: radial-gradient(circle at 35% 30%, #d0574d, #8e231d 70%)">গ</span>
            </div>
        </div>
    </div>
</section>

{{-- ============ PRICING ============ --}}
@if ($settings->bool('show_pricing') && $packages->isNotEmpty())
<section class="section" id="pricing">
    <div class="container-x">
        <x-section-head eyebrow="প্যাকেজ" title="যতদিন চান, ততদিন আপনার ঠিকানা" sub="সব প্যাকেজে যেকোনো টেমপ্লেট। পার্থক্য শুধু মেয়াদ আর ছবির সংখ্যায়।" align="center" />
        @include('site.partials.pricing-cards', ['packages' => $packages])
    </div>
</section>
@endif

{{-- ============ TESTIMONIALS ============ --}}
@if ($settings->bool('show_testimonials') && $testimonials->isNotEmpty())
<section class="section bg-white/60">
    <div class="container-x">
        <x-section-head eyebrow="যারা বানিয়েছেন" title="তাদের মুখেই শুনুন" />
        <div class="no-scrollbar -mx-4 mt-10 flex snap-x gap-5 overflow-x-auto px-4 pb-4 md:mx-0 md:grid md:grid-cols-3 md:overflow-visible md:px-0">
            @foreach ($testimonials as $i => $t)
                <figure class="reveal relative w-[82%] shrink-0 snap-center rounded-xl bg-[#fffdf7] p-6 pt-8 shadow-[var(--shadow-soft)] md:w-auto" style="transform: rotate({{ [-1.5, 1, -0.5][$i % 3] }}deg)">
                    <span aria-hidden="true" class="absolute -top-3 left-1/2 h-6 w-20 -translate-x-1/2 rotate-[-3deg] bg-holud/60"></span>
                    <blockquote class="text-lg leading-relaxed text-ink">“{{ $t->quote }}”</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        @if ($t->avatarUrl())<img src="{{ $t->avatarUrl() }}" alt="" class="h-10 w-10 rounded-full object-cover" loading="lazy" width="40" height="40">@endif
                        <span><span class="block font-semibold">{{ $t->name }}</span><span class="text-sm text-ink-mute">{{ $t->role }}</span></span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ FAQ ============ --}}
@if ($settings->bool('show_faq') && $faqs->isNotEmpty())
<section class="section" id="faq">
    <div class="container-x grid gap-10 md:grid-cols-[1fr_1.5fr]">
        <div>
            <x-section-head eyebrow="প্রশ্নোত্তর" title="যা জানতে চান" sub="আরও প্রশ্ন থাকলে আমাদের জিজ্ঞেস করুন।" />
            <a href="{{ route('contact') }}" class="btn btn-ghost mt-6">যোগাযোগ করুন</a>
        </div>
        @include('site.partials.faq-list', ['faqs' => $faqs])
    </div>
</section>
@endif

{{-- ============ FINAL CTA ============ --}}
<section class="px-4 pb-6 sm:px-6">
    <div class="night kantha-border container-x relative overflow-hidden rounded-[1.8rem] px-6 py-16 text-center [--kb:var(--color-holud)] [--kb2:#fff] sm:px-12 md:py-20">
        <x-kantha type="lotus" class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 text-holud/30 [--k2:rgb(181_51_43/.6)]" />
        <x-kantha type="lotus" class="pointer-events-none absolute -bottom-16 -right-16 h-56 w-56 text-holud/30 [--k2:rgb(181_51_43/.6)]" />
        <h2 class="relative mx-auto max-w-3xl text-[2rem] leading-snug sm:text-[3rem]">{{ $settings->get('final_cta_title') }}</h2>
        <a href="{{ route('order.create') }}" class="btn btn-gold relative mt-9 text-lg">{{ $settings->get('final_cta_button') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
    </div>
</section>
@endsection

@push('sticky')
<div x-data="{ show: false }" @scroll.window.passive="show = window.scrollY > 640 && (window.innerHeight + window.scrollY) < document.body.offsetHeight - 500"
     x-show="show" x-cloak x-transition.opacity
     class="fixed inset-x-0 bottom-0 z-40 border-t border-ink/10 bg-paper/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur md:hidden">
    <a href="{{ route('order.create') }}" class="btn btn-primary w-full">{{ $settings->get('hero_cta_primary') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
</div>
@endpush
