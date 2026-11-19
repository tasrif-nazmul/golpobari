@extends('layouts.site')
@section('content')
@php use App\Support\Bn; @endphp
<section class="px-4 pb-20 pt-10 sm:px-6">
    <div class="mx-auto max-w-xl text-center">
        <div class="mx-auto grid h-20 w-20 place-items-center rounded-full bg-sindoor/10 text-4xl">❤️</div>
        <h1 class="mt-6 text-[2rem] font-extrabold leading-tight sm:text-4xl">আপনার গল্প আমাদের কাছে পৌঁছে গেছে ❤️</h1>
        <p class="mt-3 text-lg text-ink-soft">ধন্যবাদ, {{ $order->customer_name }}! পেমেন্ট যাচাইয়ের পর আমরা পেজ তৈরি শুরু করব।</p>

        <div class="card stitch mt-8 p-6 text-left" x-data="copyText(@js($order->order_number))">
            <p class="text-sm font-semibold text-ink-mute">আপনার Order ID</p>
            <div class="mt-1 flex items-center justify-between gap-3">
                <p class="font-mono text-2xl font-bold tracking-wider sm:text-3xl">{{ $order->order_number }}</p>
                <button type="button" class="btn btn-ink btn-sm shrink-0" @click="copy()"><span x-text="copied ? 'কপি হয়েছে ✓' : 'কপি'"></span></button>
            </div>
            <p class="mt-3 rounded-xl bg-holud/20 px-3 py-2 text-sm font-medium">📌 এই Order ID-টি সংরক্ষণ করুন বা স্ক্রিনশট নিন। ট্র্যাক করতে লাগবে।</p>
            <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-ink-mute">পেমেন্ট</dt><dd class="font-semibold">{{ $order->payment_status->labelBn() }}</dd></div>
                <div><dt class="text-ink-mute">মোট</dt><dd class="font-semibold">{{ Bn::money($order->final_price) }}</dd></div>
                <div><dt class="text-ink-mute">আনুমানিক ডেলিভারি</dt><dd class="font-semibold">{{ $settings->get('delivery_time') }}</dd></div>
                <div><dt class="text-ink-mute">Transaction ID</dt><dd class="font-mono font-semibold">{{ $order->payment?->transaction_id }}</dd></div>
            </dl>
        </div>

        <div class="card mt-5 p-6 text-left">
            <h2 class="text-xl font-bold">এরপর কী হবে?</h2>
            <ol class="mt-4 space-y-3">
                @foreach (['আমরা আপনার bKash পেমেন্ট যাচাই করব।', 'আপনার তথ্য আর ছবি দিয়ে পেজ তৈরি করব।', 'পেজ লাইভ হলে আপনার ফোনে লিংক পাঠাব।', 'লিংকটা শেয়ার করুন প্রিয় মানুষের সাথে!'] as $i => $t)
                    <li class="flex gap-3"><span class="grid h-7 w-7 shrink-0 place-items-center rounded-full bg-kolapata/10 text-sm font-bold text-kolapata">{{ Bn::digits($i + 1) }}</span><span class="text-ink-soft">{{ $t }}</span></li>
                @endforeach
            </ol>
            @if ($ins = $settings->get('order_instructions'))<p class="mt-5 border-t border-dashed border-ink/10 pt-4 text-sm text-ink-soft">{{ $ins }}</p>@endif
        </div>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <a href="{{ route('track') }}" class="btn btn-ink">অর্ডার ট্র্যাক করুন</a>
            <a href="{{ route('contact') }}" class="btn btn-ghost">যোগাযোগ</a>
        </div>
    </div>
</section>
@endsection
