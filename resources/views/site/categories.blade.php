@extends('layouts.site')
@section('content')
@include('site.partials.page-hero', ['eyebrow' => 'ক্যাটাগরি', 'title' => 'কার গল্প বলবেন আজ?', 'sub' => 'প্রিয় মানুষ থেকে নিজের ব্যবসা, প্রতিটার জন্য আলাদা করে ভাবা টেমপ্লেট।'])
<section class="px-4 pb-20 sm:px-6">
    <div class="container-x grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($categories as $i => $category)
            <div class="reveal">
                <x-category-card :category="$category" :index="$i" class="min-h-[13rem]" />
                @if ($category->occasions)
                    <p class="mt-4 px-1 text-sm leading-relaxed text-ink-mute">{{ implode(' · ', $category->occasions) }}</p>
                @endif
            </div>
        @endforeach
    </div>
</section>
@endsection
