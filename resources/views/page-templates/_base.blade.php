@php
    $s = app(\App\Support\Settings::class);
    $first = collect($d->media)->flatten(1)->first();
    $ogImage = $page?->seo('og_image') ? \Illuminate\Support\Facades\Storage::disk('public')->url($page->seo('og_image')) : ($first['url'] ?? ($s->fileUrl('og_image_path') ?: asset('images/og-default.png')));
    $desc = $page?->seo('description') ?: \Illuminate\Support\Str::limit(strip_tags($d->text('intro', $d->text('about', $d->text('message', $d->text('story', $s->get('seo_description')))))), 155);
    $indexable = $page && $page->visibility === \App\Enums\PageVisibility::Public && empty($isPreview);
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $desc }}">
    @if ($page)
        <link rel="canonical" href="{{ $page->seo('canonical') ?: $page->url() }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ $page->url() }}">
        <meta property="og:site_name" content="{{ $s->get('site_name') }}">
    @endif
    <meta property="og:title" content="{{ $page?->seo('og_title') ?: $title }}">
    <meta property="og:description" content="{{ $page?->seo('og_description') ?: $desc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $page?->seo('og_title') ?: $title }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="robots" content="{{ $indexable ? 'index, follow' : 'noindex, nofollow' }}">
    <meta name="theme-color" content="{{ $d->theme('background', '#ffffff') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ $fontsUrl }}">
    @vite(['resources/css/page.css', 'resources/js/page.js'])
</head>
<body class="t-page @yield('body-class')" style="{{ $d->cssVars() }}" @if (! empty($isDraft)) data-draft @endif>
<div class="t-progress" data-progress aria-hidden="true"></div>
@if (! empty($isPreview) && empty($isDraft))
    <div class="t-preview-bar">{{ $page ? 'প্রিভিউ · এখনো পাবলিক নয়' : (! empty($isDraft) ? 'আপনার পেজের প্রিভিউ · জমা দেওয়ার পর আমরা সাজিয়ে প্রকাশ করব' : 'নমুনা প্রিভিউ · আপনার পেজে থাকবে আপনার লেখা ও ছবি') }}</div>
@endif

@yield('body')

@include('page-templates.partials.extras')

@if (! empty($music['src']))
    <div class="t-music" data-music data-src="{{ $music['src'] }}" @if (! empty($isDraft)) data-manual @endif>
        <button type="button" class="t-music__btn" data-music-toggle aria-pressed="false" aria-label="গান চালু করুন" title="{{ $music['title'] }}">
            <span class="t-music__disc" aria-hidden="true"></span>
            <span class="t-music__bars" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
            <svg class="t-music__off" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4z" fill="currentColor"/><path d="M16.5 9.5l5 5m0-5l-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <span class="t-music__hint" data-music-hint>🎵 {{ $music['title'] }}</span>
    </div>
@endif

@if ($showBranding)
    <footer class="t-branding"><a href="{{ url('/') }}" target="_blank" rel="noopener">{{ $brandingText }}</a></footer>
@endif

<div class="t-lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="ছবি">
    <button type="button" class="t-lb-close" data-lb-close aria-label="বন্ধ">✕</button>
    <button type="button" class="t-lb-prev" data-lb-prev aria-label="আগের ছবি">‹</button>
    <img alt="" data-lb-img>
    <p data-lb-caption></p>
    <button type="button" class="t-lb-next" data-lb-next aria-label="পরের ছবি">›</button>
</div>
</body>
</html>
