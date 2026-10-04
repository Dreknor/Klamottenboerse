@props(['titel' => null, 'zurueck' => null])
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ?? 'Klamottenbörse' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-lg">
<header class="flex items-center justify-between gap-3 border-b-4 border-marke-500 bg-stone-900 px-4 py-3 text-white">
    <div class="flex items-center gap-3">
        @if ($zurueck)
            <a href="{{ $zurueck }}" class="rounded-lg bg-white/10 px-4 py-2 text-white no-underline">← Zurück</a>
        @endif
        <span class="text-xl font-semibold">{{ $titel }}</span>
    </div>
    <span class="text-sm text-stone-300">{{ auth()->user()?->name }}</span>
</header>
<main class="mx-auto max-w-5xl p-4">
    <x-ui.flash />
    {{ $slot }}
</main>
</body>
</html>
