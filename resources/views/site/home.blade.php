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
{{-- ============ HERO ============ --}}
<section class="relative overflow-hidden px-4 pb-16 pt-6 sm:px-6 md:pb-24 md:pt-10">
    <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 h-[28rem] w-[28rem] rounded-full bg-holud/25 blur-3xl"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -left-32 bottom-0 h-[22rem] w-[22rem] rounded-full bg-shapla/20 blur-3xl"></div>

    <div class="container-x relative grid items-center gap-12 md:grid-cols-[1.08fr_1fr] md:gap-8">
        <div>
            <p class="chip reveal">❤️ ভালোবাসা · 🎂 জন্মদিন · 👤 পোর্টফোলিও · 💼 ব্যবসা</p>
            <h1 class="reveal mt-5 text-[2.55rem] font-extrabold leading-[1.18] sm:text-6xl lg:text-[4.35rem]">
                {{ $before }}<span class="hand-underline text-sindoor">{{ $word }}<svg viewBox="0 0 200 20" preserveAspectRatio="none" aria-hidden="true"><path d="M3 14C40 6 95 4 197 10" fill="none" stroke="#f2b134" stroke-width="6" stroke-linecap="round"/></svg></span>{{ $after }}
            </h1>
            <p class="reveal mt-5 max-w-xl text-lg leading-relaxed text-ink-soft sm:text-xl">{{ $settings->get('hero_subtitle') }}</p>

            <div class="reveal mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('order.create') }}" class="btn btn-primary text-lg">{{ $settings->get('hero_cta_primary') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
                <a href="{{ route('templates.index') }}" class="btn btn-ghost text-lg">{{ $settings->get('hero_cta_secondary') }}</a>
            </div>

            {{-- The "address" idea, made literal --}}
            <div class="reveal mt-8 inline-flex max-w-full items-center gap-2 rounded-full bg-white py-2 pl-3 pr-5 shadow-[var(--shadow-soft)] ring-1 ring-ink/5"
                 x-data="slugTyper(@js($slugs))" aria-label="উদাহরণ লিংক">
                <x-icon name="lock" class="h-4 w-4 shrink-0 text-kolapata" />
                <span class="truncate font-serif text-[1.05rem] text-ink-soft">golpobari.com/<span class="font-semibold text-ink" x-text="text"></span><span class="ml-px inline-block h-5 w-[2px] translate-y-1 animate-caret bg-sindoor" aria-hidden="true"></span></span>
            </div>
        </div>

        {{-- Floating pages --}}
        <div class="relative mx-auto h-[25rem] w-full max-w-[26rem] sm:h-[30rem] md:max-w-none" aria-hidden="true">
            <div class="absolute left-0 top-6 w-[58%] animate-float-slow" style="--r:-6deg">
                <div class="overflow-hidden rounded-2xl bg-[#fff5f3] shadow-[var(--shadow-lift)] ring-1 ring-black/5">
                    <div class="flex items-center gap-1 bg-white/80 px-2.5 py-1.5"><span class="h-1.5 w-1.5 rounded-full bg-sindoor/60"></span><span class="h-1.5 w-1.5 rounded-full bg-holud"></span><span class="ml-1 truncate text-[9px] text-ink-mute">golpobari.com/<b class="text-ink">mitu-rafi</b></span></div>
                    <div class="px-4 pb-5 pt-4 text-center text-[#7a1f3d]">
                        <p class="font-hand text-3xl leading-none">Mitu ♥ Rafi</p>
                        <img src="{{ asset('images/samples/couple-a.svg') }}" alt="" class="mx-auto mt-3 aspect-square w-24 rounded-full object-cover ring-4 ring-white" width="96" height="96">
                        <p class="mt-3 font-display text-2xl font-bold">{{ Bn::digits('1,009') }}</p>
                        <p class="text-[11px] font-semibold uppercase tracking-widest opacity-60">দিন একসাথে</p>
                        <div class="mx-auto mt-3 h-1.5 w-4/5 rounded-full bg-[#7a1f3d]/15"></div>
                        <div class="mx-auto mt-1.5 h-1.5 w-3/5 rounded-full bg-[#7a1f3d]/15"></div>
                    </div>
                </div>
            </div>
            <div class="absolute right-0 top-0 w-[50%] animate-float" style="--r:5deg; animation-delay:-2s">
                <div class="overflow-hidden rounded-2xl bg-[#fff8e7] shadow-[var(--shadow-lift)] ring-1 ring-black/5">
                    <div class="flex items-center gap-1 bg-white/80 px-2.5 py-1.5"><span class="h-1.5 w-1.5 rounded-full bg-sindoor/60"></span><span class="h-1.5 w-1.5 rounded-full bg-holud"></span><span class="ml-1 truncate text-[9px] text-ink-mute">golpobari.com/<b class="text-ink">happy-birthday</b></span></div>
                    <div class="relative px-4 pb-5 pt-4 text-center text-[#2b2d8f]">
                        <span class="absolute left-3 top-3 h-2 w-2 rotate-12 bg-[#ffb703]"></span><span class="absolute right-5 top-6 h-2 w-1 -rotate-12 bg-[#e9839f]"></span><span class="absolute left-8 top-12 h-1.5 w-1.5 rounded-full bg-[#2f6b4f]"></span>
                        <p class="text-4xl">🎂</p>
                        <p class="mt-1 font-display text-xl font-extrabold leading-tight">শুভ জন্মদিন<br>মিতু!</p>
                        <div class="mt-3 grid grid-cols-3 gap-1">
                            <img src="{{ asset('images/samples/cake.svg') }}" alt="" class="aspect-square rounded-md object-cover" width="40" height="40">
                            <img src="{{ asset('images/samples/friends.svg') }}" alt="" class="aspect-square rounded-md object-cover" width="40" height="40">
                            <img src="{{ asset('images/samples/landscape-b.svg') }}" alt="" class="aspect-square rounded-md object-cover" width="40" height="40">
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-[24%] w-[54%] animate-float" style="--r:-1.5deg; animation-delay:-4s">
                <div class="overflow-hidden rounded-2xl bg-[#fafaf7] shadow-[var(--shadow-lift)] ring-1 ring-black/5">
                    <div class="flex items-center gap-1 bg-white px-2.5 py-1.5"><span class="h-1.5 w-1.5 rounded-full bg-sindoor/60"></span><span class="h-1.5 w-1.5 rounded-full bg-holud"></span><span class="ml-1 truncate text-[9px] text-ink-mute">golpobari.com/<b class="text-ink">nodi</b></span></div>
                    <div class="flex items-center gap-3 px-4 py-4">
                        <img src="{{ asset('images/samples/portrait-a.svg') }}" alt="" class="h-12 w-12 rounded-full object-cover" width="48" height="48">
                        <div class="min-w-0 flex-1">
                            <p class="font-serif text-base font-semibold leading-tight text-ink">Nodi Rahman</p>
                            <p class="text-[10px] text-[#c2410c]">Product designer · Dhaka</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1 px-4 pb-4">
                        @foreach (['Figma', 'UX', 'Bangla type'] as $tag)<span class="rounded-full bg-ink/5 px-2 py-0.5 text-[9px] text-ink-soft">{{ $tag }}</span>@endforeach
                    </div>
                </div>
            </div>
            <p class="absolute -bottom-2 right-0 hidden rotate-[-4deg] font-hand text-2xl text-ink-soft sm:block">এটা হতে পারে আপনার গল্প ↖</p>
        </div>
    </div>
</section>

{{-- ============ WHAT IS ============ --}}
<section class="section bg-white/60" id="about">
    <div class="container-x grid items-center gap-10 md:grid-cols-2">
        <div class="reveal">
            <p class="eyebrow">ছোট্ট করে বললে</p>
            <h2 class="mt-2 text-[1.85rem] font-bold sm:text-4xl">{{ $settings->get('about_title') }}</h2>
            <p class="mt-5 text-lg leading-relaxed text-ink-soft sm:text-xl">{{ $settings->get('about_text') }}</p>
        </div>
        <div class="reveal relative mx-auto w-full max-w-md">
            {{-- An envelope addressed to a web address --}}
            <div class="relative rotate-[-2deg] rounded-2xl bg-paper-2 p-6 shadow-[var(--shadow-lift)] sm:p-8">
                <div class="absolute right-5 top-5 grid h-16 w-14 place-items-center rounded-sm border-2 border-dashed border-sindoor/50 bg-white text-2xl">🏡</div>
                <p class="font-hand text-2xl text-ink-mute">প্রাপক</p>
                <p class="mt-1 font-display text-2xl font-bold">আম্মু</p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 font-hand text-2xl text-ink-mute">ঠিকানা</p>
                <p class="mt-1 break-all font-serif text-xl text-sindoor sm:text-2xl">golpobari.com/<b>ammu</b></p>
                <div class="stitch-line mt-5 text-ink"></div>
                <p class="mt-5 text-ink-soft">ভেতরে: ৩২টা ছবি, ছোটবেলার গল্প আর একটা লম্বা ধন্যবাদ।</p>
            </div>
        </div>
    </div>
</section>

{{-- ============ CATEGORIES ============ --}}
@if ($categories->isNotEmpty())
<section class="section" id="categories">
    <div class="container-x">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <x-section-head eyebrow="কার জন্য বানাবেন?" title="প্রতিটা গল্পের আলাদা রং" sub="উপলক্ষ বেছে নিন, তারপর মানানসই টেমপ্লেট।" />
            <a href="{{ route('categories.index') }}" class="font-semibold text-sindoor underline-offset-4 hover:underline">সব ক্যাটাগরি →</a>
        </div>
        <div class="mt-10 grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
            @foreach ($categories as $i => $category)
                <x-category-card :category="$category" :index="$i" class="reveal" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ HOW IT WORKS ============ --}}
<section class="section bg-ink text-paper" id="how">
    <div class="container-x">
        <x-section-head eyebrow="কীভাবে কাজ করে" title="চারটা ধাপ, তারপর আপনার গল্প অনলাইনে" class="[&_h2]:text-paper [&_p:last-child]:text-paper/70" sub="পুরো কাজটা মোবাইল থেকেই করা যায়, ১০ মিনিটের মধ্যে।" />
        @php
            $steps = [
                ['১', 'টেমপ্লেট বাছুন', 'উপলক্ষ আর পছন্দমতো ডিজাইন বেছে নিন। প্রিভিউ দেখে নিন মোবাইল আর ডেস্কটপে।'],
                ['২', 'গল্প আর ছবি দিন', 'নাম, লেখা, স্মৃতি আর ছবিগুলো দিন। যা জানতে চাওয়া হবে শুধু সেটুকুই।'],
                ['৩', 'bKash-এ পেমেন্ট', 'Send Money করে Transaction ID দিন। কোনো কার্ড লাগবে না।'],
                ['৪', 'আমরা বানিয়ে দিই', 'যাচাইয়ের পর আমরা পেজ তৈরি করে লাইভ করি, আপনি পান নিজের লিংক।'],
            ];
        @endphp
        <ol class="relative mt-12 grid gap-8 md:grid-cols-4 md:gap-6">
            <div aria-hidden="true" class="stitch-line absolute left-[1.6rem] top-0 h-full w-[2px] text-holud md:left-0 md:top-[1.6rem] md:h-[2px] md:w-full" style="background-image: linear-gradient(180deg, currentColor 0 10px, transparent 10px 18px); background-size: 2px 18px;"></div>
            @foreach ($steps as [$n, $title, $text])
                <li class="reveal relative flex gap-5 md:block">
                    <span class="relative z-10 grid h-[3.25rem] w-[3.25rem] shrink-0 place-items-center rounded-full bg-holud font-display text-2xl font-bold text-ink">{{ $n }}</span>
                    <div class="md:mt-5">
                        <h3 class="text-xl font-bold text-paper">{{ $title }}</h3>
                        <p class="mt-2 text-paper/70">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
        <a href="{{ route('order.create') }}" class="btn btn-primary mt-12">এখনই শুরু করুন <x-icon name="arrow-right" class="h-5 w-5" /></a>
    </div>
</section>

{{-- ============ TEMPLATES ============ --}}
@if ($settings->bool('show_templates') && $templates->isNotEmpty())
<section class="section" id="templates">
    <div class="container-x">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <x-section-head eyebrow="টেমপ্লেট" title="প্রতিটা ডিজাইন আলাদা মেজাজের" sub="পছন্দ হলে সেটা দিয়েই শুরু করুন। রং আর লেখা হবে আপনার মতো।" />
            <a href="{{ route('templates.index') }}" class="font-semibold text-sindoor underline-offset-4 hover:underline">সব টেমপ্লেট →</a>
        </div>
        <div class="no-scrollbar -mx-4 mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 sm:mx-0 sm:grid sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 lg:grid-cols-3">
            @foreach ($templates as $template)
                <x-template-card :template="$template" class="w-[78%] shrink-0 snap-center sm:w-auto" />
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============ WHY ============ --}}
<section class="section bg-paper-2/70">
    <div class="container-x">
        <x-section-head eyebrow="কেন গল্পবাড়ি" title="সুন্দর, সহজ, আর একদম আপনার" align="center" />
        @php
            $why = [
                ['sparkle', 'যত্ন করে বানানো ডিজাইন', 'প্রতিটা টেমপ্লেট আলাদা করে ডিজাইন করা'],
                ['phone', 'মোবাইলে দারুণ', 'যে ফোনেই খুলুক, দেখতে সুন্দর'],
                ['code-off', 'কোনো coding না', 'আপনি শুধু গল্প বলবেন'],
                ['bolt', 'দ্রুত ডেলিভারি', $settings->get('delivery_time').'র মধ্যে'],
                ['taka', 'সাধ্যের মধ্যে', 'এক কাপ কফির দামে শুরু'],
                ['heart', 'একদম ব্যক্তিগত', 'আপনার নাম, আপনার ছবি, আপনার কথা'],
                ['shield', 'নিরাপদ', 'ছবি সুরক্ষিত, চাইলে প্রাইভেট পেজ'],
                ['link', 'শেয়ার করা সহজ', 'Messenger-এ সুন্দর প্রিভিউসহ লিংক'],
            ];
        @endphp
        <ul class="mt-12 grid grid-cols-2 gap-x-5 gap-y-8 lg:grid-cols-4">
            @foreach ($why as [$icon, $title, $text])
                <li class="reveal">
                    <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white text-sindoor shadow-[var(--shadow-soft)]"><x-icon :name="$icon" /></span>
                    <h3 class="mt-4 font-sans text-lg font-semibold">{{ $title }}</h3>
                    <p class="mt-1 text-[0.95rem] leading-snug text-ink-soft">{{ $text }}</p>
                </li>
            @endforeach
        </ul>
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
    <div class="container-x relative overflow-hidden rounded-[2rem] bg-sindoor px-6 py-16 text-center text-white sm:px-12 md:py-24">
        <div aria-hidden="true" class="pointer-events-none absolute inset-3 rounded-[1.6rem] border-2 border-dashed border-white/30"></div>
        <p class="font-hand text-2xl text-holud">শেষ কথা</p>
        <h2 class="mx-auto mt-3 max-w-3xl text-3xl font-extrabold leading-snug !text-white sm:text-5xl">{{ $settings->get('final_cta_title') }}</h2>
        <a href="{{ route('order.create') }}" class="btn mt-9 bg-white text-lg text-sindoor-dark shadow-[0_6px_0_-1px_rgb(0_0_0/0.15)] hover:bg-paper">{{ $settings->get('final_cta_button') }} <x-icon name="arrow-right" class="h-5 w-5" /></a>
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
