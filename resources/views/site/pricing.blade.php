@extends('layouts.site')
@section('content')
@include('site.partials.page-hero', ['eyebrow' => 'প্যাকেজ', 'title' => 'যতদিন চান, ততদিন আপনার ঠিকানা', 'sub' => 'একবার পেমেন্ট, কোনো লুকানো খরচ নেই। মেয়াদ শেষে চাইলে রিনিউ করবেন।'])
<section class="px-4 pb-16 sm:px-6">
    <div class="container-x">
        @include('site.partials.pricing-cards', ['packages' => $packages])
        <p class="mt-10 text-center text-ink-soft">কুপন কোড থাকলে অর্ডারের সময় দিন। পেমেন্ট শুধু <b>bKash Send Money</b>-এ।</p>
    </div>
</section>
@if ($faqs->isNotEmpty())
<section class="px-4 pb-20 sm:px-6"><div class="container-x max-w-3xl">@include('site.partials.faq-list', ['faqs' => $faqs])</div></section>
@endif
@endsection
