@extends('layouts.site', ['hideFooter' => true])
@section('content')
<section class="grid min-h-[80dvh] place-items-center px-4 py-16 text-center">
    <div class="max-w-md">
        <div class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-paper-2 text-5xl" aria-hidden="true">🔒</div>
        <h1 class="mt-8 text-3xl font-extrabold">পেজটি এখন দেখা যাচ্ছে না</h1>
        <p class="mt-4 text-lg text-ink-soft">পেজটি সাময়িকভাবে বন্ধ রাখা হয়েছে।</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-8">গল্পবাড়িতে যান</a>
    </div>
</section>
@endsection
