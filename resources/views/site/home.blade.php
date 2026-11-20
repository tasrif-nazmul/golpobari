@extends('layouts.site')

@php
    use App\Support\Bn;
    $heroTitle = $settings->get('hero_title');
    // Hand-underline the word "ঠিকানা" (or the last word) of the admin-editable headline.
    $word = str_contains($heroTitle, 'ঠিকানা') ? 'ঠিকানা' : (preg_split('/\s+/u', trim($heroTitle, " ।.!")) ?: [''])[count(preg_split('/\s+/u', trim($heroTitle, " ।.!")) ?: ['']) - 1];
    [$before, $after] = array_pad(explode($word, $heroTitle, 2), 2, '');
    $slugs = array_values(array_filter(array_map('trim', explode(',', $settings->get('slug_examples', 'korim')))));
@endphp

@section('content')
{{-- ============ HERO: a night-time courtyard, a phone that opens like a letter ============ --}}
<section class="px-2.5 pt-1 sm:px-5">
    <div class="night relative overflow-hidden rounded-[2rem] sm:rounded-[2.75rem]">
        <div class="stars"></div>
        @foreach ([[8, 70, 0], [22, 30, -3], [70, 80, -5], [88, 40, -1.5], [52, 18, -7], [35, 88, -4]] as [$x, $y, $d])
            <span class="firefly" style="left: {{ $x }}%; top: {{ $y }}%; animation-delay: {{ $d }}s" aria-hidden="true"></span>
        @endforeach
        <svg aria-hidden="true" class="spin-slow pointer-events-none absolute -right-48 top-1/2 h-[46rem] w-[46rem] -translate-y-1/2 text-gold/20 md:-right-24" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width=".7">
            @for ($i = 0; $i < 24; $i++)<ellipse cx="100" cy="46" rx="10" ry="34" transform="rotate({{ $i * 15 }} 100 100)"/>@endfor
            @for ($i = 0; $i < 12; $i++)<ellipse cx="100" cy="70" rx="7" ry="20" transform="rotate({{ $i * 30 + 15 }} 100 100)"/>@endfor
            <circle cx="100" cy="100" r="22"/><circle cx="100" cy="100" r="98" stroke-dasharray="2 5"/><circle cx="100" cy="100" r="88"/>
        </svg>

        <div class="container-x relative grid items-center gap-12 px-5 pb-14 pt-12 sm:px-8 md:grid-cols-[1.15fr_.85fr] md:gap-6 md:pb-20 md:pt-16">
            <div>
                <p class="chip-glass reveal"><span class="text-holud">✦</span> ভালোবাসা · জন্মদিন · বিয়ে · ঈদ · স্মৃতি</p>
                <h1 class="font-fancy reveal mt-6 text-[2.9rem] leading-[1.2] sm:text-[4.2rem] lg:text-[4.9rem]">
                    {{ $before }}<span class="hand-underline text-holud">{{ $word }}<svg viewBox="0 0 200 20" preserveAspectRatio="none" aria-hidden="true"><path d="M3 14C40 6 95 4 197 10" fill="none" stroke="#df4f39" stroke-width="5" stroke-linecap="round"/></svg></span>{{ $after }}
                </h1>
                <p class="reveal mt-5 max-w-xl text-lg leading-relaxed text-paper/75 sm:text-xl">{{ $settings->get('hero_subtitle') }}</p>
                <div class="reveal mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('order.create') }}" class="btn btn-gold text-lg">{{ $settings->get('hero_cta_primary') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
                    <a href="{{ route('templates.index') }}" class="btn btn-glass text-lg">{{ $settings->get('hero_cta_secondary') }}</a>
                </div>
                <ul class="reveal mt-8 flex flex-wrap gap-x-5 gap-y-2 text-[0.95rem] text-paper/80">
                    <li class="flex items-center gap-2"><span class="flex h-3.5 items-end gap-[2px] text-holud"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span> পেজে নিজের গান</li>
                    <li>💌 খাম খোলার মতো শুরু</li>
                    <li>📱 মোবাইলেই সবচেয়ে সুন্দর</li>
                </ul>
                <div class="reveal mt-7 inline-flex max-w-full items-center gap-2 rounded-full bg-white/10 py-2 pl-3 pr-5 ring-1 ring-white/15" x-data="slugTyper(@js($slugs))" aria-label="উদাহরণ লিংক">
                    <x-icon name="lock" class="h-4 w-4 shrink-0 text-holud" />
                    <span class="truncate font-serif text-[1.05rem] text-paper/70">golpobari.com/<span class="font-semibold text-paper" x-text="text"></span><span class="ml-px inline-block h-5 w-[2px] translate-y-1 animate-caret bg-holud" aria-hidden="true"></span></span>
                </div>
            </div>

            @if ($heroItems->isNotEmpty())
            <div class="relative mx-auto h-[31rem] w-full max-w-[24rem] sm:h-[36rem]" x-data="heroPhones(@js($heroItems))">
                <div class="phone absolute right-0 top-6 w-[52%] rotate-[7deg] opacity-90 sm:top-10" aria-hidden="true" style="aspect-ratio: 390/800">
                    <div class="phone__screen">
                        <template x-for="(it, k) in items" :key="k"><img :src="it.inside" alt="" class="absolute inset-0 transition-opacity duration-700" :class="k === i ? 'opacity-100' : 'opacity-0'"></template>
                    </div>
                </div>
                <button type="button" class="phone absolute left-0 top-0 z-10 w-[62%] -rotate-[4deg] text-left transition hover:-rotate-[2deg]" style="aspect-ratio: 390/800" @click="$dispatch('tpl-preview', cur.payload)" :aria-label="(cur.name || '') + ' লাইভ প্রিভিউ'">
                    <div class="phone__screen">
                        <template x-for="(it, k) in items" :key="k"><img :src="it.cover" alt="" class="absolute inset-0 transition-opacity duration-700" :class="k === i ? 'opacity-100' : 'opacity-0'"></template>
                    </div>
                </button>
                <div class="absolute -left-2 bottom-16 z-20 inline-flex items-center gap-2 rounded-2xl bg-white px-3.5 py-2.5 text-sm font-semibold text-ink shadow-[var(--shadow-lift)] sm:-left-8">
                    <span class="grid h-8 w-8 place-items-center rounded-full bg-night text-holud"><span class="flex h-3.5 items-end gap-[2px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i><i class="eq-bar [animation-delay:-.15s]"></i></span></span>
                    <span class="leading-tight">গান বাজছে<br><span class="text-xs font-normal text-ink-mute">খোলার সাথে সাথেই</span></span>
                </div>
                <button type="button" class="absolute bottom-2 right-2 z-20 inline-flex items-center gap-2 rounded-full bg-holud px-4 py-2.5 text-sm font-bold text-night shadow-lg" @click="$dispatch('tpl-preview', cur.payload)">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg> <span x-text="(cur.name || '') + ' লাইভ দেখুন'"></span>
                </button>
            </div>
            @endif
        </div>
    </div>
</section>

{{-- ============ OCCASIONS MARQUEE ============ --}}
<div class="overflow-hidden py-7" aria-hidden="true">
    <div class="marquee gap-3">
        @foreach (array_merge(...array_fill(0, 2, ['💞 প্রিয় মানুষের জন্য', '🎂 জন্মদিন', '💍 বিবাহবার্ষিকী', '🌹 প্রপোজ', '🌙 ঈদ মোবারক', '🌼 শুভ নববর্ষ', '💛 গায়ে হলুদ', '📖 স্মৃতির খাতা', '✈️ ভ্রমণের গল্প', '🪔 শ্রদ্ধাঞ্জলি', '🎁 সারপ্রাইজ', '💼 পোর্টফোলিও'])) as $o)
            <span class="stitch-frame mx-1.5 inline-flex shrink-0 items-center rounded-full bg-white px-5 py-2.5 font-semibold text-ink-soft shadow-[var(--shadow-soft)]">{{ $o }}</span>
        @endforeach
    </div>
</div>

{{-- ============ TEMPLATES ============ --}}
@if ($settings->bool('show_templates') && $templates->isNotEmpty())
<section class="section !pt-6" id="templates">
    <div class="container-x">
        <div class="mx-auto max-w-2xl text-center">
            <p class="ornament justify-center text-sm font-semibold tracking-widest">আমাদের নকশা</p>
            <h2 class="font-fancy mt-3 text-[2.4rem] leading-tight sm:text-6xl">প্রতিটা গল্পের আলাদা <span class="text-sindoor">সাজ</span></h2>
            <p class="mt-4 text-lg text-ink-soft">ছবিতে ছুঁয়ে দেখুন, প্রতিটা টেমপ্লেট আসল পেজের মতো খোলে, বাজে আর নড়ে।</p>
        </div>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($templates as $template)
                <x-template-card :template="$template" class="reveal" />
            @endforeach
        </div>
        @if ($categories->isNotEmpty())
            <div class="mt-12 flex flex-wrap items-center justify-center gap-2.5">
                <span class="mr-1 font-semibold text-ink-soft">উপলক্ষ ধরে খুঁজুন:</span>
                @foreach ($categories as $c)
                    <a href="{{ route('categories.show', $c) }}" class="chip !bg-white !py-2 ring-1 ring-ink/10 transition hover:ring-ink/30">{{ $c->emoji }} {{ $c->displayName() }}</a>
                @endforeach
            </div>
        @endif
        <div class="mt-8 text-center">
            <a href="{{ route('templates.index') }}" class="btn btn-ink">সব টেমপ্লেট দেখুন <x-icon name="arrow-right" class="h-5 w-5" /></a>
        </div>
    </div>
</section>
@endif

{{-- ============ MUSIC ============ --}}
<section class="px-2.5 sm:px-5">
    <div class="night relative overflow-hidden rounded-[2rem] sm:rounded-[2.75rem]">
        <div class="stars opacity-70"></div>
        <div class="container-x relative grid items-center gap-10 px-5 py-14 sm:px-8 md:grid-cols-2 md:py-20">
            <div>
                <p class="ornament text-sm font-semibold tracking-widest">শুধু পেজ না</p>
                <h2 class="font-fancy mt-3 text-[2.3rem] leading-tight sm:text-5xl">পেজ খুললেই বাজবে <span class="text-holud">আপনার গান</span></h2>
                <p class="mt-4 text-lg leading-relaxed text-paper/75">আমাদের নিজেদের বানানো সুর থেকে বেছে নিন, অথবা নিজের পছন্দের গানটা দিয়ে দিন। পেজের কোণে থাকবে চালু-বন্ধের ছোট্ট বোতাম।</p>
                <p class="mt-3 text-sm text-paper/50">সব সুর গল্পবাড়ির জন্য আলাদা করে তৈরি, তাই কপিরাইটের ঝামেলা নেই।</p>
            </div>
            <ul class="grid gap-2.5 sm:grid-cols-2" x-data="trackList">
                @foreach ($tracks as $t)
                    <li>
                        <button type="button" @click="toggle(@js($t['key']), @js($t['url']))" class="flex w-full items-center gap-3 rounded-2xl bg-white/[0.07] p-3 text-left ring-1 ring-white/10 transition hover:bg-white/[0.12]" :class="playing === @js($t['key']) && '!bg-white/[0.16] ring-holud/60'">
                            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full transition" :class="playing === @js($t['key']) ? 'bg-holud text-night' : 'bg-white/10 text-paper'">
                                <svg x-show="playing !== @js($t['key'])" class="ml-0.5 h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4.5v15l13-7.5z"/></svg>
                                <span x-show="playing === @js($t['key'])" class="flex h-4 items-end gap-[3px]"><i class="eq-bar"></i><i class="eq-bar [animation-delay:-.3s]"></i><i class="eq-bar [animation-delay:-.6s]"></i></span>
                            </span>
                            <span class="min-w-0 leading-tight"><b class="block text-paper">{{ $t['icon'] }} {{ $t['title'] }}</b><span class="block truncate text-xs text-paper/55">{{ $t['mood'] }}</span></span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- ============ HOW IT WORKS ============ --}}
<section class="section" id="how">
    <div class="container-x">
        <div class="mx-auto max-w-2xl text-center">
            <p class="ornament justify-center text-sm font-semibold tracking-widest">কীভাবে কাজ করে</p>
            <h2 class="font-fancy mt-3 text-[2.4rem] leading-tight sm:text-6xl">তিনটা ধাপ, তারপর <span class="text-sindoor">সারপ্রাইজ</span></h2>
            <p class="mt-4 text-lg text-ink-soft">পুরো কাজটা মোবাইল থেকেই করা যায়, ১০ মিনিটের মধ্যে।</p>
        </div>
        @php
            $steps = [
                ['১', '🎨', 'টেমপ্লেট বাছুন', 'উপলক্ষ অনুযায়ী ডিজাইন বেছে নিন। লাইভ প্রিভিউতে খুলে, শুনে দেখে নিন।'],
                ['২', '✍️', 'গল্প, ছবি আর গান দিন', 'প্রতিটা ঘরের পাশে দেখাবে লেখাটা পেজের কোথায় বসবে। bKash-এ পেমেন্ট করুন।'],
                ['৩', '💌', 'লিংক পাঠিয়ে দিন', 'আমরা পেজ সাজিয়ে লাইভ করি। আপনি পান নিজের লিংক, প্রিয় মানুষ পায় একটা খাম।'],
            ];
        @endphp
        <ol class="mt-14 grid gap-6 md:grid-cols-3">
            @foreach ($steps as [$n, $emoji, $title, $text])
                <li class="reveal stitch-frame relative rounded-[1.75rem] bg-white p-7 pt-9 text-ink shadow-[var(--shadow-soft)]">
                    <span class="font-fancy absolute -top-7 left-6 grid h-14 w-14 place-items-center rounded-full bg-night text-3xl text-holud shadow-lg">{{ $n }}</span>
                    <span class="absolute right-6 top-6 text-4xl" aria-hidden="true">{{ $emoji }}</span>
                    <h3 class="mt-3 text-xl font-bold">{{ $title }}</h3>
                    <p class="mt-2 text-ink-soft">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-12 text-center"><a href="{{ route('order.create') }}" class="btn btn-primary">এখনই শুরু করুন <x-icon name="arrow-right" class="h-5 w-5" /></a></div>
    </div>
</section>

{{-- ============ WHAT IS + WHY ============ --}}
<section class="section bg-paper-2/70" id="about">
    <div class="container-x grid items-center gap-12 md:grid-cols-2">
        <div class="reveal">
            <p class="eyebrow">ছোট্ট করে বললে</p>
            <h2 class="mt-2 text-[1.85rem] font-bold sm:text-4xl">{{ $settings->get('about_title') }}</h2>
            <p class="mt-5 text-lg leading-relaxed text-ink-soft sm:text-xl">{{ $settings->get('about_text') }}</p>
            @php
                $why = [
                    ['sparkle', 'যত্ন করে বানানো ডিজাইন'], ['phone', 'যে ফোনেই খুলুক, সুন্দর'], ['bolt', $settings->get('delivery_time').'র মধ্যে ডেলিভারি'],
                    ['shield', 'চাইলে প্রাইভেট পেজ'], ['link', 'Messenger-এ সুন্দর প্রিভিউ'], ['heart', 'একদম আপনার মতো'],
                ];
            @endphp
            <ul class="mt-8 grid grid-cols-2 gap-x-4 gap-y-4">
                @foreach ($why as [$icon, $title])
                    <li class="flex items-center gap-3"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-white text-sindoor shadow-[var(--shadow-soft)]"><x-icon :name="$icon" class="h-5 w-5" /></span><span class="font-medium leading-snug">{{ $title }}</span></li>
                @endforeach
            </ul>
        </div>
        <div class="reveal relative mx-auto w-full max-w-md">
            {{-- An envelope addressed to a web address --}}
            <div class="relative rotate-[-2deg] rounded-2xl bg-[#fffaf1] p-6 shadow-[var(--shadow-lift)] sm:p-8" style="background-image: var(--grain-img)">
                <div class="absolute right-5 top-5 grid h-16 w-14 place-items-center rounded-sm border-2 border-dashed border-sindoor/50 bg-white text-2xl">🏡</div>
                <p class="font-hand text-2xl text-ink-mute">প্রাপক</p>
                <p class="mt-1 font-display text-2xl font-bold">আম্মু</p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 font-hand text-2xl text-ink-mute">ঠিকানা</p>
                <p class="mt-1 break-all font-serif text-xl text-sindoor sm:text-2xl">golpobari.com/<b>ammu</b></p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 text-ink-soft">ভেতরে: ৩২টা ছবি, ছোটবেলার গল্প, একটা প্রিয় গান আর একটা লম্বা ধন্যবাদ।</p>
                <span class="absolute -bottom-6 -left-5 grid h-16 w-16 rotate-[-12deg] place-items-center rounded-full font-fancy text-2xl text-white shadow-lg" style="background: radial-gradient(circle at 35% 30%, #e86a6a, #9c1c2c 70%)">গ</span>
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
    <div class="container-x relative overflow-hidden rounded-[2rem] bg-sindoor px-6 py-16 text-center text-white sm:px-12 md:py-24" style="background-image: radial-gradient(70% 90% at 50% 0%, rgb(255 255 255 / .18), transparent 60%), var(--grain-img)">
        <div aria-hidden="true" class="pointer-events-none absolute inset-3 rounded-[1.6rem] border-2 border-dashed border-white/30"></div>
        @foreach ([[8, 20], [88, 30], [15, 75], [80, 78]] as [$x, $y])<span aria-hidden="true" class="absolute text-3xl opacity-40" style="left: {{ $x }}%; top: {{ $y }}%">✿</span>@endforeach
        <p class="font-fancy text-3xl text-holud">শেষ কথা</p>
        <h2 class="font-fancy mx-auto mt-3 max-w-3xl text-[2.3rem] leading-snug !text-white sm:text-6xl">{{ $settings->get('final_cta_title') }}</h2>
        <a href="{{ route('order.create') }}" class="btn btn-gold mt-9 text-lg">{{ $settings->get('final_cta_button') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
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
