{{-- Falling (or rising) decorations. Params: $chars (array), $count (14), $rise (bool), $class --}}
@php $chars = $chars ?? ['✿']; $count = $count ?? 14; @endphp
<div class="t-particles {{ ! empty($rise) ? 't-particles--rise' : '' }} {{ $class ?? '' }}" aria-hidden="true">
    @for ($i = 0; $i < $count; $i++)
        <span style="left: {{ ($i * 37 + 7) % 100 }}%; animation-duration: {{ 9 + ($i * 7) % 8 }}s; animation-delay: -{{ ($i * 1.7) % 12 }}s; font-size: {{ 12 + ($i * 5) % 14 }}px">{{ $chars[$i % count($chars)] }}</span>
    @endfor
</div>
