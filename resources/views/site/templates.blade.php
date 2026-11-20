@extends('layouts.site')
@section('content')
@include('site.partials.page-hero', ['eyebrow' => 'টেমপ্লেট', 'title' => 'আপনার গল্পের পোশাক বাছুন', 'sub' => 'যেকোনো ডিজাইনে ছুঁয়ে লাইভ দেখুন: খাম খোলে, গান বাজে, ছবি নড়ে। পছন্দ হলে সেটা দিয়েই শুরু।'])
<div class="sticky top-16 z-30 bg-paper/90 px-4 py-3 backdrop-blur sm:px-6 md:top-[4.5rem]">
    <nav class="container-x no-scrollbar flex gap-2 overflow-x-auto" aria-label="ক্যাটাগরি ফিল্টার">
        <a href="{{ route('templates.index') }}" class="chip shrink-0 !py-2 {{ ! $current ? '!bg-ink !text-paper' : 'bg-white ring-1 ring-ink/10' }}">সব</a>
        @foreach ($categories as $c)
            <a href="{{ route('templates.index', ['category' => $c->slug]) }}" class="chip shrink-0 !py-2 {{ $current?->id === $c->id ? '!bg-ink !text-paper' : 'bg-white ring-1 ring-ink/10' }}">{{ $c->emoji }} {{ $c->displayName() }}</a>
        @endforeach
    </nav>
</div>
<section class="px-4 pb-20 pt-6 sm:px-6">
    <div class="container-x">
        @if ($templates->isEmpty())
            <p class="card p-10 text-center text-ink-soft">এই ক্যাটাগরিতে এখনো টেমপ্লেট নেই।</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)<x-template-card :template="$template" class="reveal" />@endforeach
            </div>
        @endif
    </div>
</section>
@endsection
