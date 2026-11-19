@extends('layouts.site')
@section('content')
@include('site.partials.page-hero', ['title' => $page->title])
<section class="px-4 pb-20 sm:px-6">
    <article class="container-x prose-golpo max-w-3xl text-lg leading-relaxed">{!! $page->html() !!}</article>
    <p class="container-x mt-10 max-w-3xl text-sm text-ink-mute">সর্বশেষ হালনাগাদ: {{ \App\Support\Bn::date($page->updated_at) }}</p>
</section>
@endsection
