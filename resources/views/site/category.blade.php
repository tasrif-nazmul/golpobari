@extends('layouts.site')
@section('content')
<section class="px-4 pb-8 pt-8 sm:px-6 md:pt-14">
    <div class="container-x">
        <a href="{{ route('categories.index') }}" class="text-sm font-semibold text-ink-mute hover:text-ink">← সব ক্যাটাগরি</a>
        <div class="mt-4 flex items-start gap-4">
            <span class="text-5xl" aria-hidden="true">{{ $category->emoji }}</span>
            <div>
                <h1 class="text-[2.1rem] font-extrabold leading-tight sm:text-5xl">{{ $category->displayName() }}</h1>
                <p class="mt-2 max-w-2xl text-lg text-ink-soft">{{ $category->description }}</p>
            </div>
        </div>
        @if ($category->occasions)
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($category->occasions as $o)<span class="chip bg-white ring-1 ring-ink/10">{{ $o }}</span>@endforeach
            </div>
        @endif
    </div>
</section>
<section class="px-4 pb-20 sm:px-6">
    <div class="container-x">
        @if ($templates->isEmpty())
            <div class="card p-10 text-center">
                <p class="text-lg text-ink-soft">এই ক্যাটাগরির টেমপ্লেট শীঘ্রই আসছে।</p>
                <a href="{{ route('templates.index') }}" class="btn btn-ink mt-6">অন্য টেমপ্লেট দেখুন</a>
            </div>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($templates as $template)<x-template-card :template="$template" class="reveal" />@endforeach
            </div>
        @endif
    </div>
</section>
@endsection
