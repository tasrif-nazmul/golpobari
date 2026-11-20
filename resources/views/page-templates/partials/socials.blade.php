@php $links = collect($d->items('social_links'))->filter(fn ($l) => $d->link($l['url'] ?? null)); @endphp
@if ($links->isNotEmpty())
<ul class="t-socials {{ $class ?? '' }}">
    @foreach ($links as $l)
        <li><a href="{{ $d->link($l['url']) }}" target="_blank" rel="noopener nofollow">{{ $l['platform'] ?: 'Link' }}</a></li>
    @endforeach
</ul>
@endif
