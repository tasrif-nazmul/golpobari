{{-- Procedural alpona (Bengali floor art) mandala. Params: $class, $rings (3), $petals (12), $stroke (1.4) --}}
@php
    $rings = $rings ?? 3; $petals = $petals ?? 12; $sw = $stroke ?? 1.4;
@endphp
<svg viewBox="-100 -100 200 200" class="t-alpona {{ $class ?? '' }}" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="{{ $sw }}" stroke-linecap="round">
    <circle r="7" fill="currentColor" stroke="none" opacity=".8"/>
    <circle r="12"/>
    @for ($r = 1; $r <= $rings; $r++)
        @php
            $inner = 12 + ($r - 1) * 28; $outer = $inner + 26; $n = $petals * $r;
            $mid = ($inner + $outer) / 2; $w = min(14, (2 * M_PI * $mid / $n) * 0.55);
        @endphp
        @for ($i = 0; $i < $n; $i++)
            <path transform="rotate({{ round(360 / $n * $i + ($r % 2 ? 0 : 180 / $n), 2) }})" d="M0,-{{ $inner }} C{{ round($w, 1) }},-{{ round($inner + 8, 1) }} {{ round($w * .7, 1) }},-{{ round($outer - 6, 1) }} 0,-{{ $outer }} C-{{ round($w * .7, 1) }},-{{ round($outer - 6, 1) }} -{{ round($w, 1) }},-{{ round($inner + 8, 1) }} 0,-{{ $inner }}Z"/>
            @if ($r === $rings)<circle transform="rotate({{ round(360 / $n * $i + 180 / $n, 2) }})" cy="-{{ $outer + 4 }}" r="1.8" fill="currentColor" stroke="none"/>@endif
        @endfor
        <circle r="{{ $outer + ($r === $rings ? 9 : 1) }}" stroke-dasharray="{{ $r % 2 ? '0' : '2 4' }}" opacity=".7"/>
    @endfor
</svg>
