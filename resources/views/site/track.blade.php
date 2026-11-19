@extends('layouts.site')
@section('content')
<section class="px-4 pb-20 pt-10 sm:px-6">
    <div class="mx-auto max-w-md">
        <p class="eyebrow">অর্ডার ট্র্যাক</p>
        <h1 class="mt-2 text-[2rem] font-extrabold">আপনার গল্প কোথায় আছে?</h1>
        <p class="mt-2 text-ink-soft">Order ID আর অর্ডারের সময় দেওয়া মোবাইল নম্বর দিন।</p>
        @if (session('status'))<p class="mt-5 rounded-2xl bg-kolapata/10 px-4 py-3 font-medium text-kolapata">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('track.lookup') }}" class="card mt-6 space-y-5 p-6" novalidate>
            @csrf
            <div>
                <label class="field-label" for="order_number">Order ID</label>
                <input id="order_number" name="order_number" value="{{ old('order_number') }}" class="input font-mono uppercase" placeholder="GB-20261119-1024" required maxlength="30" autocapitalize="characters" @error('order_number') aria-invalid="true" @enderror>
                @error('order_number')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="field-label" for="phone">মোবাইল নম্বর</label>
                <input id="phone" name="phone" value="{{ old('phone') }}" class="input" type="tel" inputmode="tel" placeholder="01XXXXXXXXX" required maxlength="20" @error('phone') aria-invalid="true" @enderror>
                @error('phone')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="btn btn-primary w-full">খুঁজে দেখুন</button>
        </form>
    </div>
</section>
@endsection
