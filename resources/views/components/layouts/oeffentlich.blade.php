@props(['titel' => null, 'beschreibung' => null])
@php $verein = \App\Support\Einstellungen::get('vereinsname'); @endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (\App\Support\Demo::aktiv())<meta name="robots" content="noindex, nofollow">@endif
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#FF6900">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ? $titel.' – ' : '' }}{{ $verein }}</title>
    <meta name="description" content="{{ $beschreibung ?? 'Sortierter Kindersachenflohmarkt zugunsten des Ev. Kinderhauses Radebeul – organisiert von ehrenamtlichen Eltern.' }}">
    <meta property="og:title" content="{{ $titel ?? $verein }}">
    <meta property="og:image" content="{{ asset('images/logo-640.png') }}">
    <link rel="icon" href="{{ asset('images/logo-640.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="oeffentlich flex min-h-screen flex-col">
<x-demo-hinweis />
<a href="#inhalt" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:rounded focus:bg-white focus:px-3 focus:py-2">Zum Inhalt springen</a>
<header class="border-b border-stone-200 bg-white" x-data="{ menu: false }">
    <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('start') }}" class="shrink-0" aria-label="Zur Startseite">
            <img src="{{ asset('images/logo-schriftzug.png') }}" alt="Klamottenbörse des Evangelischen Kinderhauses" class="h-14 w-auto sm:h-16" width="480" height="170">
        </a>
        <button type="button" class="rounded-lg border border-stone-300 px-3 py-1.5 sm:hidden" @click="menu = !menu" :aria-expanded="menu" aria-label="Menü">☰</button>
        <nav class="hidden gap-5 text-sm font-medium sm:flex" aria-label="Hauptmenü">
            @foreach ($menue as $eintrag)
                <a href="{{ route('seite', $eintrag->slug) }}" @if (request()->is($eintrag->slug)) aria-current="page" @endif>{{ $eintrag->titel }}</a>
            @endforeach
            <a href="{{ route('anmeldung.create') }}">Als Verkäufer anmelden</a>
            <a href="{{ route('helfer.index') }}">Helfen</a>
            <a href="{{ route('portal.index') }}">Mein Portal</a>
        </nav>
    </div>
    <nav x-show="menu" x-cloak class="flex flex-col gap-1 border-t border-stone-100 px-4 py-2 sm:hidden" aria-label="Hauptmenü mobil">
        @foreach ($menue as $eintrag)
            <a href="{{ route('seite', $eintrag->slug) }}" class="py-2">{{ $eintrag->titel }}</a>
        @endforeach
        <a href="{{ route('anmeldung.create') }}" class="py-2">Als Verkäufer anmelden</a>
        <a href="{{ route('helfer.index') }}" class="py-2">Helfen</a>
        <a href="{{ route('portal.index') }}" class="py-2">Mein Portal</a>
    </nav>
</header>

<main id="inhalt" class="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
    <x-ui.flash />
    {{ $slot }}
</main>

<footer class="border-t border-stone-200 bg-white">
    <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-stone-600 sm:flex-row">
        <span>{{ $verein }}</span>
        <nav class="flex flex-wrap justify-center gap-4" aria-label="Rechtliches">
            <a href="{{ route('impressum') }}">Impressum</a>
            <a href="{{ route('datenschutz') }}">Datenschutz</a>
            <button type="button" class="text-marke-700 hover:underline" onclick="window.dispatchEvent(new CustomEvent('cookie-einstellungen-oeffnen'))">Cookie-Einstellungen</button>
            <a href="{{ route('login') }}">Team-Login</a>
        </nav>
    </div>
</footer>

<x-ui.cookie-hinweis />
</body>
</html>
