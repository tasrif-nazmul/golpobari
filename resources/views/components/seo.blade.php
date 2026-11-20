@props(['seo' => []])
@php
    $s = app(\App\Support\Settings::class);
    $title = $seo['title'] ?? $s->get('seo_title');
    $desc = \Illuminate\Support\Str::limit(strip_tags($seo['description'] ?? $s->get('seo_description')), 160);
    $canonical = $seo['canonical'] ?? url()->current();
    $image = $seo['image'] ?? ($s->fileUrl('og_image_path') ?: asset('images/og-default.png'));
    $type = $seo['type'] ?? 'website';
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $desc }}">
<link rel="canonical" href="{{ $canonical }}">
@if (! empty($seo['noindex']))<meta name="robots" content="noindex, nofollow">@endif
<meta property="og:site_name" content="{{ $s->get('site_name') }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:title" content="{{ $seo['og_title'] ?? $title }}">
<meta property="og:description" content="{{ $seo['og_description'] ?? $desc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:locale" content="bn_BD">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo['og_title'] ?? $title }}">
<meta name="twitter:description" content="{{ $seo['og_description'] ?? $desc }}">
<meta name="twitter:image" content="{{ $image }}">
@if ($v = $s->get('google_site_verification'))<meta name="google-site-verification" content="{{ $v }}">@endif
@if (! empty($seo['jsonld']))
<script type="application/ld+json">{!! json_encode($seo['jsonld'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endif
