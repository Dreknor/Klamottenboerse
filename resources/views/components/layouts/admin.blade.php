@props(['titel' => null])
@php
    $navigation = [
        'Börse' => [
            ['Übersicht', 'admin.dashboard', 'admin.dashboard'],
            ['Verkäufer & Nummern', 'admin.teilnahmen.index', 'admin.teilnahmen.*'],
            ['Reservierungen', 'admin.reservierungen.index', 'admin.reservierungen.*'],
            ['Helfer & Schichten', 'admin.schichten.index', 'admin.schichten.*'],
            ['Verkäufe & Stornos', 'admin.verkaeufe.index', 'admin.verkaeufe.*'],
            ['Abrechnung', 'admin.abrechnung.index', 'admin.abrechnung.*'],
            ['Statistik', 'admin.statistik.index', 'admin.statistik.*'],
            ['Feedback', 'admin.feedback.index', 'admin.feedback.*'],
            ['Börsen verwalten', 'admin.boersen.index', 'admin.boersen.*'],
        ],
        'Team' => [
            ['Aufgaben & Checkliste', 'admin.aufgaben.index', 'admin.aufgaben.*'],
            ['Kalender', 'admin.kalender.index', 'admin.kalender.*'],
            ['Personen', 'admin.personen.index', 'admin.personen.index|admin.personen.show|admin.personen.edit|admin.personen.create'],
            ['Datenschutz: Inaktive', 'admin.personen.inaktive', 'admin.personen.inaktive'],
        ],
        'Kommunikation' => [
            ['Posteingang', 'admin.posteingang.index', 'admin.posteingang.*'],
            ['Mailplan', 'admin.mailplan.index', 'admin.mailplan.*'],
            ['Postausgang', 'admin.postausgang.index', 'admin.postausgang.*'],
            ['Mailvorlagen', 'admin.mailvorlagen.index', 'admin.mailvorlagen.*'],
        ],
        'Website' => [
            ['Impressum & Datenschutz', 'admin.seiten.index', 'admin.seiten.*'],
        ],
        'Vor Ort' => [
            ['Kasse', 'kasse.index', 'kasse.*'],
            ['Tablet: Annahme & Ausgabe', 'tablet.index', 'tablet.*'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titel ? $titel.' – ' : '' }}Klamottenbörse Orga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ menu: false }">
<div class="min-h-screen lg:flex">
    {{-- Seitenleiste --}}
    <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full overflow-y-auto border-r border-stone-200 bg-white transition lg:static lg:translate-x-0"
           :class="menu && 'translate-x-0'">
        <div class="border-b border-stone-200 px-4 py-4">
            <a href="{{ route('admin.dashboard') }}" class="block text-lg font-bold text-marke-700 no-underline">Klamottenbörse</a>
            <form method="post" action="{{ route('admin.boerse.wechseln') }}" class="mt-3">
                @csrf
                <label for="boerse-wechsler" class="text-xs font-medium uppercase tracking-wide text-stone-500">Aktuelle Börse</label>
                <select id="boerse-wechsler" name="boerse_id" class="feld mt-1 py-1.5 text-sm" onchange="this.form.submit()">
                    @forelse ($alleBoersen as $b)
                        <option value="{{ $b->id }}" @selected($aktuelleBoerse?->id === $b->id)>{{ $b->titel }}</option>
                    @empty
                        <option>Noch keine Börse</option>
                    @endforelse
                </select>
            </form>
        </div>
        <nav class="px-2 py-3 text-sm">
            @foreach ($navigation as $gruppe => $eintraege)
                <p class="mt-3 px-2 text-xs font-medium uppercase tracking-wide text-stone-400">{{ $gruppe }}</p>
                @foreach ($eintraege as [$text, $route, $muster])
                    @php $aktiv = request()->routeIs(...explode('|', $muster)); $ziel = route($route); @endphp
                    <a href="{{ $ziel }}"
                       class="mt-0.5 flex items-center justify-between rounded-lg px-2 py-1.5 no-underline {{ $aktiv ? 'bg-marke-50 font-medium text-marke-800' : 'text-stone-700 hover:bg-stone-100' }}">
                        <span>{{ $text }}</span>
                        @if ($route === 'admin.posteingang.index' && $offenePost > 0)
                            <x-ui.abzeichen farbe="red">{{ $offenePost }}</x-ui.abzeichen>
                        @endif
                    </a>
                @endforeach
            @endforeach
            @role('admin')
                <a href="{{ route('admin.einstellungen.edit') }}" class="mt-4 block rounded-lg px-2 py-1.5 text-stone-700 no-underline hover:bg-stone-100">Einstellungen</a>
            @endrole
        </nav>
        <div class="border-t border-stone-200 px-4 py-3 text-sm text-stone-600">
            <a href="{{ route('admin.konto.edit') }}" class="block truncate text-stone-700 no-underline hover:underline">{{ auth()->user()->name }} · Mein Konto</a>
            <form method="post" action="{{ route('logout') }}">@csrf<button class="text-marke-700 hover:underline">Abmelden</button></form>
        </div>
    </aside>
    <div class="fixed inset-0 z-20 bg-black/30 lg:hidden" x-show="menu" x-cloak @click="menu = false"></div>

    <div class="flex-1">
        <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-stone-200 bg-white px-4 py-3 lg:hidden">
            <button type="button" class="rounded-lg border border-stone-300 px-3 py-1.5" @click="menu = true" aria-label="Menü öffnen">☰</button>
            <span class="font-semibold">{{ $aktuelleBoerse?->titel ?? 'Klamottenbörse' }}</span>
        </header>
        <main class="mx-auto max-w-6xl px-4 py-6 lg:px-8">
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
