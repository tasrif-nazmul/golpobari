<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex, nofollow">
    <title>Sign in · GolpoBari Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    @vite(['resources/css/admin.css'])
</head>
<body class="grid min-h-dvh place-items-center bg-stone-100 p-4">
<main class="w-full max-w-sm">
    <div class="mb-6 flex items-center justify-center gap-2.5 text-stone-900"><x-logo class="h-10 w-10" /><span class="text-xl font-bold">GolpoBari Admin</span></div>
    <form method="POST" action="{{ route('admin.login.attempt') }}" class="a-card space-y-4 p-6">
        @csrf
        @if (session('status'))<p class="rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">{{ session('status') }}</p>@endif
        <x-admin.field name="email" label="Email" type="email" required autocomplete="username" autofocus />
        <x-admin.field name="password" label="Password" type="password" required autocomplete="current-password" />
        <x-admin.field name="remember" label="Keep me signed in on this device" type="checkbox" />
        <button class="a-btn a-btn-primary w-full">Sign in</button>
    </form>
    <p class="mt-4 text-center text-xs text-stone-500">Too many failed attempts lock sign-in for a minute.</p>
</main>
</body>
</html>
