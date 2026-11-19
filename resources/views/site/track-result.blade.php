@extends('layouts.site')
@section('content')
@php
    use App\Enums\OrderStatus;
    use App\Support\Bn;
    $steps = OrderStatus::trackingSteps();
    $status = $order->status->value;
    $currentIndex = collect($steps)->search(fn ($s) => in_array($status, $s['statuses'], true));
    $halted = in_array($status, ['cancelled', 'refunded'], true);
    $live = $order->page && $order->page->isLive();
@endphp
<section class="px-4 pb-20 pt-10 sm:px-6">
    <div class="mx-auto max-w-xl">
        <a href="{{ route('track') }}" class="text-sm font-semibold text-ink-mute hover:text-ink">← অন্য অর্ডার</a>
        <h1 class="mt-3 font-mono text-2xl font-bold sm:text-3xl">{{ $order->order_number }}</h1>
        <p class="mt-1 text-ink-soft">{{ $order->templateName() }} · {{ $order->packageName() }} · {{ Bn::money($order->final_price) }}</p>

        @if ($halted)
            <div class="card mt-6 p-6"><p class="text-lg font-bold">{{ $order->status->labelBn() }}</p><p class="mt-1 text-ink-soft">কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন।</p></div>
        @else
            <ol class="card mt-6 space-y-0 p-6">
                @foreach ($steps as $i => $s)
                    @php $done = $currentIndex !== false && $i < $currentIndex; $now = $currentIndex === $i; @endphp
                    <li class="relative flex gap-4 pb-6 last:pb-0">
                        @unless ($loop->last)<span class="absolute left-[0.95rem] top-9 h-[calc(100%-2.25rem)] w-0.5 {{ $done ? 'bg-kolapata' : 'bg-ink/10' }}"></span>@endunless
                        <span class="relative grid h-8 w-8 shrink-0 place-items-center rounded-full text-sm font-bold {{ $done ? 'bg-kolapata text-white' : ($now ? 'bg-sindoor text-white ring-4 ring-sindoor/20' : 'bg-ink/10 text-ink-mute') }}">{{ $done ? '✓' : Bn::digits($i + 1) }}</span>
                        <div class="pt-1"><p class="font-semibold {{ $now ? 'text-ink' : ($done ? 'text-ink-soft' : 'text-ink-mute') }}">{{ $s['label'] }}</p>
                            @if ($now)<p class="text-sm text-ink-soft">{{ $order->status->labelBn() }}</p>@endif</div>
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($live)
            <div class="card stitch mt-5 p-6" x-data="copyText(@js($order->page->url()))">
                <p class="text-sm font-semibold text-ink-mute">আপনার পেজ লাইভ 🎉</p>
                <a href="{{ $order->page->url() }}" target="_blank" class="mt-1 block break-all font-serif text-xl text-sindoor underline underline-offset-4">{{ preg_replace('#^https?://#', '', $order->page->url()) }}</a>
                <p class="mt-1 text-sm text-ink-soft">মেয়াদ: {{ Bn::date($order->page->expires_at) }} পর্যন্ত</p>
                <button type="button" class="btn btn-ink btn-sm mt-4" @click="copy()"><span x-text="copied ? 'কপি হয়েছে ✓' : 'লিংক কপি করুন'"></span></button>
            </div>
        @elseif ($order->page && $order->page->hasExpired())
            <div class="card mt-5 p-6"><p class="font-semibold">পেজের মেয়াদ শেষ হয়েছে।</p><p class="mt-1 text-sm text-ink-soft">রিনিউ করতে আমাদের সাথে যোগাযোগ করুন। আপনার তথ্য সংরক্ষিত আছে।</p></div>
        @endif

        @if ($order->payment_status->value === 'rejected')
            <div class="card mt-5 p-6">
                <p class="text-lg font-bold text-sindoor-dark">পেমেন্ট যাচাই করা যায়নি</p>
                @if ($order->payment?->rejection_reason)<p class="mt-1 text-ink-soft">কারণ: {{ $order->payment->rejection_reason }}</p>@endif
                <form method="POST" action="{{ route('track.payment') }}" class="mt-5 space-y-4" novalidate>
                    @csrf
                    <p class="text-sm text-ink-soft">সঠিক তথ্য দিয়ে আবার জমা দিন। পাঠাতে হবে: <b>{{ Bn::money($order->final_price) }}</b> → <b>{{ $settings->bkashNumber() }}</b></p>
                    <input name="payment_sender" class="input" type="tel" inputmode="tel" placeholder="bKash নম্বর (01XXXXXXXXX)" value="{{ old('payment_sender') }}" required>
                    @error('payment_sender')<p class="field-error">{{ $message }}</p>@enderror
                    <input name="payment_trx" class="input font-mono uppercase" placeholder="Transaction ID" value="{{ old('payment_trx') }}" required>
                    @error('payment_trx')<p class="field-error">{{ $message }}</p>@enderror
                    <input name="payment_amount" class="input" type="number" step="0.01" inputmode="decimal" placeholder="টাকার পরিমাণ" value="{{ old('payment_amount', (float) $order->final_price) }}" required>
                    <button class="btn btn-primary w-full">আবার জমা দিন</button>
                </form>
            </div>
        @endif

        <p class="mt-8 text-center text-sm text-ink-soft">প্রশ্ন আছে? <a href="{{ route('contact') }}" class="font-semibold text-sindoor underline underline-offset-4">যোগাযোগ করুন</a></p>
    </div>
</section>
@endsection
