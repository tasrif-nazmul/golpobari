@extends('layouts.site')
@section('content')
@php
    $phone = $settings->get('contact_phone'); $email = $settings->get('contact_email');
    $wa = preg_replace('/\D/', '', (string) $settings->get('whatsapp_number'));
    $waUrl = $wa ? 'https://wa.me/'.(str_starts_with($wa, '01') ? '88'.$wa : $wa) : null;
    $channels = array_filter([
        $phone ? ['ফোন', $phone, 'tel:'.preg_replace('/[^0-9+]/', '', $phone), '📞'] : null,
        $waUrl ? ['WhatsApp', $settings->get('whatsapp_number'), $waUrl, '💬'] : null,
        ($m = $settings->get('messenger_url')) ? ['Messenger', 'মেসেজ পাঠান', $m, '💌'] : null,
        ($f = $settings->get('facebook_url')) ? ['Facebook', 'আমাদের পেজ', $f, '👍'] : null,
        $email ? ['ইমেইল', $email, 'mailto:'.$email, '✉️'] : null,
    ]);
@endphp
@include('site.partials.page-hero', ['eyebrow' => 'যোগাযোগ', 'title' => 'কথা বলি?', 'sub' => 'অর্ডার নিয়ে প্রশ্ন, বিশেষ অনুরোধ বা শুধু হ্যালো বলতে।'])
<section class="px-4 pb-20 sm:px-6">
    <div class="container-x grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($channels as [$label, $value, $href, $emoji])
            <a href="{{ $href }}" @if (str_starts_with($href, 'http')) target="_blank" rel="noopener" @endif class="card stitch flex items-center gap-4 p-6 text-ink transition hover:-translate-y-1">
                <span class="text-3xl" aria-hidden="true">{{ $emoji }}</span>
                <span class="min-w-0"><span class="block text-sm font-semibold text-ink-mute">{{ $label }}</span><span class="block truncate text-lg font-semibold">{{ $value }}</span></span>
            </a>
        @empty
            <p class="card p-8 text-ink-soft sm:col-span-2 lg:col-span-3">যোগাযোগের তথ্য শীঘ্রই যোগ করা হবে।</p>
        @endforelse
    </div>
    <div class="container-x mt-8">
        <div class="card p-6 sm:p-8">
            <h2 class="text-xl font-bold">অফিস সময়</h2>
            <p class="mt-2 text-ink-soft">{{ $settings->get('office_hours') }}</p>
            <p class="mt-4 text-ink-soft">অর্ডারের অবস্থা জানতে <a href="{{ route('track') }}" class="font-semibold text-sindoor underline underline-offset-4">অর্ডার ট্র্যাক</a> করুন।</p>
        </div>
    </div>
</section>
@endsection
