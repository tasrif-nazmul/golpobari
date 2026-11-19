@extends('layouts.site', ['hideFooter' => true])
@section('content')
<section class="grid min-h-[80dvh] place-items-center px-4 py-16 text-center">
    <div class="max-w-md">
        <div class="mx-auto grid h-24 w-24 place-items-center rounded-full bg-paper-2 text-5xl" aria-hidden="true">🕯️</div>
        <h1 class="mt-8 text-3xl font-extrabold leading-snug sm:text-4xl">এই গল্পটির সময়কাল শেষ হয়েছে।</h1>
        <p class="mt-4 text-lg text-ink-soft">গল্পটা হারিয়ে যায়নি, শুধু একটু বিশ্রামে আছে। পেজের মালিক চাইলে আবার খুলে দিতে পারবেন।</p>
        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary">নিজের গল্প বানান</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost">রিনিউ করতে যোগাযোগ</a>
        </div>
    </div>
</section>
@endsection
