{{--
    Full-screen opening cover. Params:
      $style   curtain (default) | envelope | book
      $icon, $eyebrow, $title, $sub, $btn, $mode (confetti|hearts|''), $fg, $bg
      $initial letter on the wax seal (envelope) · $paper, $ribbon, $seal colours · $flower (book)
--}}
@php
    $style = $style ?? 'curtain';
    $initial = $initial ?? mb_substr(trim(preg_replace('/[^\p{L}]+/u', ' ', $title ?? '')), 0, 1) ?: '♥';
    $vars = collect([
        '--gate-bg' => $bg ?? null, '--gate-fg' => $fg ?? null,
        '--paper' => $paper ?? null, '--ribbon' => $ribbon ?? null, '--seal' => $seal ?? null,
    ])->filter()->map(fn ($v, $k) => $k.':'.$v)->implode(';');
@endphp
<div class="t-gate t-gate--{{ $style }}" data-gate="{{ $mode ?? '' }}" data-gate-style="{{ $style }}" role="dialog" aria-modal="true" aria-label="{{ $title }}" style="{{ $vars }}">
@if ($style === 'envelope')
    <div class="t-cover-cloth" aria-hidden="true"></div>
    <div class="t-gate__inner">
        @if (! empty($eyebrow))<p class="t-cover-eyebrow">{{ $eyebrow }}</p>@endif
        <p class="t-cover-title">{{ $title }}</p>
        <button type="button" class="t-env" data-gate-open aria-label="{{ $btn ?? 'খুলে দেখো' }}">
            <span class="t-env__back"></span>
            <span class="t-env__card"><span>{{ $icon ?? '💌' }}</span></span>
            <span class="t-env__front"></span>
            <span class="t-env__top"></span>
            <span class="t-env__ribbon"></span>
            <span class="t-env__seal"><b>{{ $initial }}</b></span>
        </button>
        <p class="t-cover-hint"><span aria-hidden="true">👆</span> {{ $btn ?? 'সিলমোহর ছুঁয়ে খোলো' }}</p>
    </div>
@elseif ($style === 'gift')
    <div class="t-cover-cloth" aria-hidden="true"></div>
    <div class="t-gate__inner">
        @if (! empty($eyebrow))<p class="t-cover-eyebrow">{{ $eyebrow }}</p>@endif
        <p class="t-cover-title">{{ $title }}</p>
        <button type="button" class="t-gift" data-gate-open aria-label="{{ $btn ?? 'উপহার খোলো' }}">
            <span class="t-gift__shadow"></span>
            <span class="t-gift__box"></span>
            <span class="t-gift__rib t-gift__rib--v"></span>
            <span class="t-gift__lid"><span class="t-gift__rib t-gift__rib--lid"></span></span>
            <svg class="t-gift__bow" viewBox="0 0 120 70" aria-hidden="true">
                <defs><linearGradient id="gbGift" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".5"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".25"/></linearGradient></defs>
                <path d="M60 40C40 10 8 4 8 24c0 16 28 20 52 16Z" fill="var(--ribbon)"/><path d="M60 40C40 10 8 4 8 24c0 16 28 20 52 16Z" fill="url(#gbGift)"/>
                <path d="M60 40c20-30 52-36 52-16 0 16-28 20-52 16Z" fill="var(--ribbon)"/><path d="M60 40c20-30 52-36 52-16 0 16-28 20-52 16Z" fill="url(#gbGift)"/>
                <path d="M26 20c10 4 22 12 30 18M94 20c-10 4-22 12-30 18" stroke="#000" stroke-opacity=".18" stroke-width="2" fill="none"/>
                <ellipse cx="60" cy="40" rx="9" ry="9.5" fill="var(--ribbon)"/><ellipse cx="60" cy="40" rx="9" ry="9.5" fill="url(#gbGift)"/>
            </svg>
            <span class="t-gift__tag"><span>{{ $sub ?? '' }}</span></span>
        </button>
        <p class="t-cover-hint"><span aria-hidden="true">👆</span> {{ $btn ?? 'ফিতে টেনে উপহার খোলো' }}</p>
    </div>
@elseif ($style === 'book')
    <div class="t-cover-cloth" aria-hidden="true"></div>
    <div class="t-gate__inner">
        <button type="button" class="t-book" data-gate-open aria-label="{{ $btn ?? 'খুলে দেখো' }}">
            <span class="t-book__pages"></span>
            <span class="t-book__first"><span>{{ $sub ?? '' }}</span></span>
            <span class="t-book__cover">
                <span class="t-book__frame">
                    @if (! empty($eyebrow))<span class="t-book__eyebrow">{{ $eyebrow }}</span>@endif
                    <span class="t-book__title">{{ $title }}</span>
                    <span class="t-book__orn" aria-hidden="true">❦</span>
                </span>
            </span>
            <span class="t-book__ribbon"></span>
            <svg class="t-book__bow" viewBox="0 0 120 70" aria-hidden="true">
                <defs><linearGradient id="gbBow" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity=".45"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity=".25"/></linearGradient></defs>
                <path d="M60 35C40 8 10 4 8 22c-2 16 26 20 52 13Z" fill="var(--ribbon, #c0392b)"/><path d="M60 35C40 8 10 4 8 22c-2 16 26 20 52 13Z" fill="url(#gbBow)"/>
                <path d="M60 35c20-27 50-31 52-13 2 16-26 20-52 13Z" fill="var(--ribbon, #c0392b)"/><path d="M60 35c20-27 50-31 52-13 2 16-26 20-52 13Z" fill="url(#gbBow)"/>
                <path d="M57 36 40 68l10-3 8 5 2-32Zm6 0 17 32-10-3-8 5-2-32Z" fill="var(--ribbon, #c0392b)"/><path d="M57 36 40 68l10-3 8 5 2-32Zm6 0 17 32-10-3-8 5-2-32Z" fill="#000" opacity=".12"/>
                <ellipse cx="60" cy="35" rx="8" ry="9" fill="var(--ribbon, #c0392b)"/><ellipse cx="60" cy="35" rx="8" ry="9" fill="url(#gbBow)"/>
            </svg>
            @if (($flower ?? true) !== false)
                <svg class="t-book__flower" viewBox="0 0 100 140" aria-hidden="true">
                    <path d="M50 60C52 90 46 112 40 138" stroke="#5d7a4a" stroke-width="2.4" fill="none" stroke-linecap="round"/>
                    <path d="M47 100c-12-6-20-2-24 4 10 2 18 2 24-4Zm3-18c10-8 19-6 23-1-9 4-17 5-23 1Z" fill="#6f8f57"/>
                    @for ($i = 0; $i < 8; $i++)
                        <ellipse cx="50" cy="38" rx="9" ry="22" fill="{{ $flowerColor ?? '#e9a0b5' }}" transform="rotate({{ $i * 45 }} 50 58) translate(0 -4)" opacity=".92"/>
                    @endfor
                    <circle cx="50" cy="58" r="8" fill="#f2c14e"/><circle cx="50" cy="58" r="8" fill="url(#gbBow)"/>
                </svg>
            @endif
        </button>
        <p class="t-cover-hint"><span aria-hidden="true">👆</span> {{ $btn ?? 'ফিতে খুলে দেখো' }}</p>
    </div>
@else
    <div class="t-gate__half t-gate__half--l"></div>
    <div class="t-gate__half t-gate__half--r"></div>
    <div class="t-gate__inner">
        @if (! empty($icon))<div class="t-bob text-6xl" aria-hidden="true">{{ $icon }}</div>@endif
        @if (! empty($eyebrow))<p class="t-eyebrow mt-6 opacity-80">{{ $eyebrow }}</p>@endif
        <p class="font-heading mt-3 text-4xl leading-tight sm:text-5xl">{{ $title }}</p>
        @if (! empty($sub))<p class="mt-3 text-lg opacity-85">{{ $sub }}</p>@endif
        <button type="button" class="t-gate__btn" data-gate-open>{{ $btn ?? 'খুলে দেখো' }} <span aria-hidden="true">→</span></button>
    </div>
@endif
</div>
