<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') · GolpoBari</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600&family=Noto+Serif+Bengali:wght@700;800&family=Caveat:wght@600&display=swap">
    {{-- Self-contained on purpose: error pages must render even when the app, database or build is broken. --}}
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100dvh;display:grid;place-items:center;background:#fbf5ec;color:#1f1a16;font-family:'Hind Siliguri',system-ui,sans-serif;padding:24px;text-align:center}
        .wrap{max-width:440px}.code{font-family:'Caveat',cursive;font-size:96px;line-height:1;color:#d9482b;margin:0}
        h1{font-family:'Noto Serif Bengali',serif;font-size:clamp(26px,6vw,36px);line-height:1.3;margin:12px 0 10px}
        p{color:#5c5249;font-size:18px;line-height:1.7;margin:0}
        .btns{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:28px}
        a{display:inline-flex;align-items:center;min-height:52px;padding:0 24px;border-radius:999px;font-weight:600;text-decoration:none}
        .p{background:#d9482b;color:#fff}.g{background:#fff;color:#1f1a16;box-shadow:inset 0 0 0 1px rgba(31,26,22,.15)}
        .stitch{border:2px dashed rgba(31,26,22,.15);border-radius:28px;padding:40px 24px}
    </style>
</head>
<body>
<main class="wrap stitch">
    <p class="code">@yield('code')</p>
    <h1>@yield('heading')</h1>
    <p>@yield('message')</p>
    <div class="btns">
        <a class="p" href="{{ url('/') }}">হোমে ফিরুন</a>
        @hasSection('secondary')@yield('secondary')@endif
    </div>
</main>
</body>
</html>
