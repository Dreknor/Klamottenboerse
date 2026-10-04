@props(['titel' => null])
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ? $titel.' – ' : '' }}{{ \App\Support\Einstellungen::get('vereinsname') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col">
<header class="border-b border-stone-200 bg-white">
    <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-3 px-4 py-4">
        <a href="{{ route('start') }}" class="text-xl font-bold text-marke-700 no-underline">Klamottenbörse</a>
        <nav class="flex flex-wrap gap-4 text-sm">
            <a href="{{ route('anmeldung.create') }}">Als Verkäufer anmelden</a>
            <a href="{{ route('helfer.index') }}">Helfen</a>
            <a href="{{ route('portal.index') }}">Mein Portal</a>
        </nav>
    </div>
</header>
<main class="mx-auto w-full max-w-4xl flex-1 px-4 py-8">
    <x-ui.flash />
    {{ $slot }}
</main>
<footer class="border-t border-stone-200 bg-white py-6 text-center text-sm text-stone-500">
    {{ \App\Support\Einstellungen::get('vereinsname') }}
</footer>
</body>
</html>
