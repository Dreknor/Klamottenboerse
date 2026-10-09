@props(['titel' => null, 'zurueck' => null, 'zurueckText' => 'Zurück', 'vermerk' => null])
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (\App\Support\Demo::aktiv())<meta name="robots" content="noindex, nofollow">@endif
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#FF6900">
    <link rel="apple-touch-icon" href="{{ \App\Support\Datei::url('images/icon-192.png') }}">
    <link rel="icon" href="{{ \App\Support\Datei::url('favicon.ico') }}" sizes="48x48">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ?? 'Klamottenbörse' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-lg">
<x-demo-hinweis />
<header class="flex items-center justify-between gap-3 border-b-4 border-marke-500 bg-stone-900 px-4 py-3 text-white">
    <div class="flex items-center gap-3">
        @if ($zurueck)
            <a href="{{ $zurueck }}" class="rounded-lg bg-white/10 px-4 py-2 text-white no-underline">← {{ $zurueckText }}</a>
        @endif
        <span class="text-xl font-semibold">{{ $titel }}</span>
    </div>
    <div class="flex items-center gap-3">
        @if ($vermerk)
            {{-- Schnellzugang Reputation: Kiste fehlt, Termin verpasst, defekte Ware … --}}
            <a href="{{ route('vermerk.schnell', ['quelle' => $vermerk, 'zurueck' => url()->full()]) }}" class="rounded-lg bg-white/10 px-4 py-2 text-white no-underline">⚑ Vermerk</a>
        @endif
        <span class="text-sm text-stone-300">{{ auth()->user()?->name }}</span>
    </div>
</header>
<main class="mx-auto max-w-5xl p-4">
    <x-ui.flash />
    {{ $slot }}
</main>
</body>
</html>
