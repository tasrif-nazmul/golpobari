@extends('layouts.site')
@section('content')
@include('site.partials.page-hero', ['eyebrow' => 'প্রশ্নোত্তর', 'title' => 'যা জানতে চান', 'sub' => 'উত্তর না পেলে সরাসরি জিজ্ঞেস করুন।'])
<section class="px-4 pb-20 sm:px-6">
    <div class="container-x max-w-3xl">
        @include('site.partials.faq-list', ['faqs' => $faqs])
        <div class="mt-10 text-center"><a href="{{ route('contact') }}" class="btn btn-ink">যোগাযোগ করুন</a></div>
    </div>
</section>
@endsection
