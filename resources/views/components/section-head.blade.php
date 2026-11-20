@props(['eyebrow' => null, 'title', 'sub' => null, 'align' => 'left'])
<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto text-center ' : '').'max-w-2xl']) }}>
    @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    <h2 class="font-fancy mt-2 text-[2.2rem] leading-snug sm:text-5xl">{{ $title }}</h2>
    @if ($sub)<p class="mt-3 text-lg text-ink-soft">{{ $sub }}</p>@endif
</div>
