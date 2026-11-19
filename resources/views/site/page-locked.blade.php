@extends('layouts.site', ['hideFooter' => true])
@section('content')
<section class="grid min-h-[80dvh] place-items-center px-4 py-16">
    <div class="w-full max-w-sm text-center">
        <div class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-shapla/20 text-5xl" aria-hidden="true">💌</div>
        <h1 class="mt-8 text-3xl font-extrabold">একটা ব্যক্তিগত গল্প</h1>
        <p class="mt-3 text-ink-soft">এই পেজটি দেখতে একটা কোড লাগবে। যিনি লিংক পাঠিয়েছেন তার কাছে জেনে নিন।</p>
        <form method="POST" action="{{ route('page.unlock', $page->slug) }}" class="mt-8 space-y-3 text-left">
            @csrf
            <label for="access_code" class="sr-only">কোড</label>
            <input id="access_code" name="access_code" type="password" class="input text-center text-lg tracking-widest" placeholder="••••••" required autofocus autocomplete="off" @error('access_code') aria-invalid="true" @enderror>
            @error('access_code')<p class="field-error text-center">{{ $message }}</p>@enderror
            <button class="btn btn-primary w-full">খুলুন</button>
        </form>
    </div>
</section>
@endsection
