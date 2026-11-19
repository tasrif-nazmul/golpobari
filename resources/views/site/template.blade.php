@extends('layouts.site')
@section('content')
<section class="px-4 pb-6 pt-6 sm:px-6 md:pt-10" x-data="{ device: window.innerWidth < 768 ? 'mobile' : 'desktop' }">
    <div class="container-x">
        <a href="{{ route('templates.index', ['category' => $template->category->slug]) }}" class="text-sm font-semibold text-ink-mute hover:text-ink">← {{ $template->category->displayName() }}</a>
        <div class="mt-3 flex flex-col justify-between gap-5 md:flex-row md:items-end">
            <div>
                <h1 class="font-serif text-[2.1rem] font-bold leading-tight sm:text-5xl">{{ $template->name }}</h1>
                <p class="mt-2 max-w-2xl text-lg text-ink-soft">{{ $template->description }}</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="inline-flex rounded-full bg-white p-1 ring-1 ring-ink/10" role="group" aria-label="প্রিভিউ সাইজ">
                    <button type="button" @click="device = 'mobile'" :aria-pressed="device === 'mobile'" :class="device === 'mobile' ? 'bg-ink text-paper' : 'text-ink-soft'" class="inline-flex h-10 items-center gap-1.5 rounded-full px-4 text-sm font-semibold"><x-icon name="mobile" class="h-4 w-4" /> মোবাইল</button>
                    <button type="button" @click="device = 'desktop'" :aria-pressed="device === 'desktop'" :class="device === 'desktop' ? 'bg-ink text-paper' : 'text-ink-soft'" class="inline-flex h-10 items-center gap-1.5 rounded-full px-4 text-sm font-semibold"><x-icon name="desktop" class="h-4 w-4" /> ডেস্কটপ</button>
                </div>
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <div class="transition-all duration-500" :class="device === 'mobile' ? 'w-[min(390px,100%)]' : 'w-full'">
                <div class="overflow-hidden bg-white shadow-[var(--shadow-lift)] ring-1 ring-ink/10" :class="device === 'mobile' ? 'rounded-[2.2rem] border-[10px] border-ink' : 'rounded-2xl'">
                    <div x-show="device === 'desktop'" class="flex items-center gap-1.5 border-b border-ink/5 bg-paper-2/60 px-4 py-2.5">
                        <span class="h-2.5 w-2.5 rounded-full bg-sindoor/70"></span><span class="h-2.5 w-2.5 rounded-full bg-holud/80"></span><span class="h-2.5 w-2.5 rounded-full bg-kolapata/60"></span>
                        <span class="ml-3 rounded-full bg-white px-3 py-0.5 text-xs text-ink-mute">golpobari.com/<b class="text-ink">your-name</b></span>
                    </div>
                    <iframe src="{{ route('templates.render', $template) }}" title="{{ $template->name }} প্রিভিউ" loading="lazy"
                            class="block w-full bg-white" :class="device === 'mobile' ? 'h-[720px] max-h-[75vh]' : 'h-[78vh] min-h-[560px]'"></iframe>
                </div>
            </div>
        </div>
        <p class="mt-4 text-center text-sm text-ink-mute">নমুনা লেখা ও ছবি দেখানো হচ্ছে। আপনার পেজে থাকবে আপনার গল্প।</p>
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
<div class="sticky bottom-0 z-40 border-t border-ink/10 bg-paper/95 px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-3 backdrop-blur">
    <div class="container-x flex items-center justify-between gap-4">
        <p class="hidden font-semibold sm:block">{{ $template->name }} পছন্দ হয়েছে?</p>
        <a href="{{ route('order.create', ['template' => $template->slug]) }}" class="btn btn-primary w-full sm:w-auto">এই টেমপ্লেট দিয়ে বানাই <x-icon name="arrow-right" class="h-5 w-5" /></a>
    </div>
</div>
@endpush
