{{-- Fields an admin added to this template that the layout doesn't place itself. --}}
@php $extras = $d->extras(); @endphp
@if ($extras)
<section class="t-extras">
    @foreach ($extras as $x)
        <div class="t-extras__block t-reveal">
            <h2>{{ $x['field']['label'] }}</h2>
            @if (in_array($x['field']['type'], ['image', 'gallery'], true))
                @include('page-templates.partials.gallery', ['items' => $x['media']])
            @elseif ($x['field']['type'] === 'repeater')
                <ul>
                    @foreach ((array) $x['value'] as $row)
                        <li>{{ implode(' · ', array_filter(array_map('strval', (array) $row))) }}</li>
                    @endforeach
                </ul>
            @elseif ($x['field']['type'] === 'url' && $d->link($x['value']))
                <p><a href="{{ $d->link($x['value']) }}" target="_blank" rel="noopener nofollow">{{ $x['value'] }}</a></p>
            @else
                <p>{!! \App\Support\TemplateData::lines($x['value']) !!}</p>
            @endif
        </div>
    @endforeach
</section>
@endif
