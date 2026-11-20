@props(['eyebrow' => null, 'title', 'sub' => null, 'align' => 'left'])
<div {{ $attributes->merge(['class' => ($align === 'center' ? 'mx-auto text-center ' : '').'max-w-2xl']) }}>
    @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
    <h2 class="mt-2 text-[1.85rem] font-bold sm:text-4xl">{{ $title }}</h2>
    @if ($sub)<p class="mt-3 text-lg text-ink-soft">{{ $sub }}</p>@endif
</div>
