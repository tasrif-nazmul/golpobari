{{--
    Nakshi-kantha style motifs drawn as running stitches (dashed strokes).
    <x-kantha type="lotus|vine|corner" class="..." />  — colours come from currentColor and --k2.
--}}
@props(['type' => 'lotus'])
@if ($type === 'lotus')
<svg {{ $attributes->merge(['viewBox' => '0 0 200 200', 'fill' => 'none', 'aria-hidden' => 'true']) }} stroke-linecap="round" stroke-linejoin="round">
    <g stroke="currentColor" stroke-width="2.2" stroke-dasharray="5 4">
        @for ($i = 0; $i < 8; $i++)
            <path d="M100 100C88 78 88 52 100 30c12 22 12 48 0 70Z" transform="rotate({{ $i * 45 }} 100 100)"/>
        @endfor
        <circle cx="100" cy="100" r="86"/>
    </g>
    <g stroke="var(--k2, currentColor)" stroke-width="2" stroke-dasharray="3 4">
        @for ($i = 0; $i < 8; $i++)
            <path d="M100 100C94 86 94 70 100 56c6 14 6 30 0 44Z" transform="rotate({{ $i * 45 + 22.5 }} 100 100)"/>
        @endfor
        <circle cx="100" cy="100" r="16"/>
        <circle cx="100" cy="100" r="94"/>
    </g>
    <g fill="var(--k2, currentColor)">
        <circle cx="100" cy="100" r="5"/>
        @for ($i = 0; $i < 16; $i++)<circle cx="100" cy="10" r="2.4" transform="rotate({{ $i * 22.5 }} 100 100)"/>@endfor
    </g>
</svg>
@elseif ($type === 'vine')
<svg {{ $attributes->merge(['viewBox' => '0 0 240 24', 'fill' => 'none', 'aria-hidden' => 'true', 'preserveAspectRatio' => 'none']) }} stroke-linecap="round">
    <path d="M2 12c20-10 40 10 60 0s40-10 60 0 40 10 60 0 40-10 56 0" stroke="currentColor" stroke-width="2" stroke-dasharray="5 4"/>
    @foreach ([32, 92, 152, 212] as $x)
        <path d="M{{ $x }} 12c-4-7-1-11 4-11 0 5-1 9-4 11Zm0 0c4 7 1 11-4 11 0-5 1-9 4-11Z" stroke="var(--k2, currentColor)" stroke-width="1.6" stroke-dasharray="2.5 2.5"/>
    @endforeach
</svg>
@elseif ($type === 'corner')
<svg {{ $attributes->merge(['viewBox' => '0 0 100 100', 'fill' => 'none', 'aria-hidden' => 'true']) }} stroke-linecap="round">
    <path d="M6 94V40C6 20 20 6 40 6h54" stroke="currentColor" stroke-width="2" stroke-dasharray="5 4"/>
    <path d="M16 94V44c0-16 12-28 28-28h50" stroke="var(--k2, currentColor)" stroke-width="1.6" stroke-dasharray="3 4"/>
    <path d="M30 30c8-14 22-14 26-6-8 2-16 2-26 6Zm0 0c-14 8-14 22-6 26 2-8 2-16 6-26Z" stroke="currentColor" stroke-width="1.8" stroke-dasharray="3 3"/>
    <circle cx="30" cy="30" r="3" fill="var(--k2, currentColor)"/>
</svg>
@endif
