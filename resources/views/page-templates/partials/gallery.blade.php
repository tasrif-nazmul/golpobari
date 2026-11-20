@php $items = $items ?? []; $style = $style ?? 'grid'; @endphp
@if ($items)
<div class="t-gallery t-gallery--{{ $style }}" data-gallery>
    @foreach ($items as $i => $img)
        <figure class="t-gallery__item t-reveal" style="--i: {{ $i }}">
            <button type="button" data-lb-open="{{ $i }}" data-src="{{ $img['url'] }}" data-caption="{{ $img['caption'] }}" aria-label="ছবি বড় করে দেখুন">
                <img src="{{ $img['thumb'] ?? $img['url'] }}" data-full="{{ $img['url'] }}" alt="{{ $img['caption'] ?: ($alt ?? '') }}" loading="lazy" decoding="async"
                     @if (! empty($img['width'])) width="{{ $img['width'] }}" height="{{ $img['height'] }}" @endif>
            </button>
            @if (! empty($img['caption']) && ($captions ?? true))<figcaption>{{ $img['caption'] }}</figcaption>@endif
        </figure>
    @endforeach
</div>
@endif
