@props(['layout' => '', 'theme' => []])
@php
    $bg = $theme['background'] ?? '#fff'; $p = $theme['primary'] ?? '#111'; $a = $theme['accent'] ?? '#e2553f'; $t = $theme['text'] ?? '#111';
@endphp
{{-- Lightweight CSS sketch used until a real screenshot is uploaded. --}}
<div class="absolute inset-0 flex flex-col items-center gap-3 p-6" style="background: {{ $bg }}; color: {{ $t }}" aria-hidden="true">
    <div class="mt-4 h-20 w-20 rounded-full" style="background: linear-gradient(135deg, {{ $a }}, {{ $p }})"></div>
    <div class="h-3 w-2/3 rounded-full" style="background: {{ $p }}"></div>
    <div class="h-2 w-1/2 rounded-full opacity-40" style="background: {{ $t }}"></div>
    <div class="mt-4 grid w-full grid-cols-3 gap-2">
        @for ($i = 0; $i < 6; $i++)
            <div class="aspect-square rounded-lg" style="background: {{ $i % 2 ? $a : $p }}; opacity: {{ 0.25 + ($i % 3) * 0.2 }}"></div>
        @endfor
    </div>
    <div class="mt-2 h-2 w-full rounded-full opacity-20" style="background: {{ $t }}"></div>
    <div class="h-2 w-5/6 rounded-full opacity-20" style="background: {{ $t }}"></div>
</div>
