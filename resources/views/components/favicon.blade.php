@if ($fav = app(\App\Support\Settings::class)->fileUrl('favicon_path'))
<link rel="icon" href="{{ $fav }}">
@else
<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
@endif
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
