{{-- Full-screen opening curtain. Params: $icon, $title, $sub, $btn, $mode (confetti|hearts|''), $fg, $bg --}}
<div class="t-gate" data-gate="{{ $mode ?? '' }}" role="dialog" aria-modal="true" aria-label="{{ $title }}" style="{{ ! empty($bg) ? '--gate-bg:'.$bg.';' : '' }}{{ ! empty($fg) ? '--gate-fg:'.$fg.';' : '' }}">
    <div class="t-gate__half t-gate__half--l"></div>
    <div class="t-gate__half t-gate__half--r"></div>
    <div class="t-gate__inner">
        @if (! empty($icon))<div class="t-bob text-6xl" aria-hidden="true">{{ $icon }}</div>@endif
        @if (! empty($eyebrow))<p class="t-eyebrow mt-6 opacity-80">{{ $eyebrow }}</p>@endif
        <p class="font-heading mt-3 text-4xl leading-tight sm:text-5xl">{{ $title }}</p>
        @if (! empty($sub))<p class="mt-3 text-lg opacity-85">{{ $sub }}</p>@endif
        <button type="button" class="t-gate__btn" data-gate-open>{{ $btn ?? 'খুলে দেখো' }} <span aria-hidden="true">→</span></button>
    </div>
</div>
