@php
    $s = app(\App\Support\Settings::class);
    $links = array_filter([
        'Facebook' => $s->get('facebook_url'),
        'Messenger' => $s->get('messenger_url'),
        'Instagram' => $s->get('instagram_url'),
        'YouTube' => $s->get('youtube_url'),
        'TikTok' => $s->get('tiktok_url'),
        'WhatsApp' => ($w = preg_replace('/\D/', '', (string) $s->get('whatsapp_number'))) ? 'https://wa.me/'.(str_starts_with($w, '01') ? '88'.$w : $w) : null,
    ], fn ($u) => $u && preg_match('#^https?://#', $u));
@endphp
@if ($links)
<ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
    @foreach ($links as $name => $url)
        <li><a href="{{ $url }}" target="_blank" rel="noopener" class="inline-flex h-10 items-center rounded-full px-4 text-sm ring-1 ring-current/25 hover:ring-current">{{ $name }}</a></li>
    @endforeach
</ul>
@endif
