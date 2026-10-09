@props(['titel' => null])
@php
    /*
     | Navigation: wenige Hauptpunkte. Zusammengehörige Seiten hängen als „Reiter“ an einem Punkt
     | und werden oben auf der Seite umgeschaltet. Eintrag: [Text, Route, Routen-Muster, Reiter?, nurAdmin?]
     */
    $navigation = [
        'Diese Börse' => [
            ['Verkäufer & Nummern', 'admin.teilnahmen.index', 'admin.teilnahmen.*|admin.reservierungen.*|admin.listen.*', [
                ['Verkäufer & Nummern', 'admin.teilnahmen.index', 'admin.teilnahmen.*'],
                ['Reservierte Nummern', 'admin.reservierungen.index', 'admin.reservierungen.*'],
                ['Listen & Drucken', 'admin.listen.index', 'admin.listen.*'],
            ]],
            ['Helfer & Schichten', 'admin.schichten.index', 'admin.schichten.*'],
            ['Verkauf & Abrechnung', 'admin.verkaeufe.index', 'admin.verkaeufe.*|admin.abrechnung.*', [
                ['Verkäufe & Stornos', 'admin.verkaeufe.index', 'admin.verkaeufe.*'],
                ['Abrechnung & Auszahlung', 'admin.abrechnung.index', 'admin.abrechnung.*'],
            ]],
            ['Auswertung', 'admin.statistik.index', 'admin.statistik.*|admin.feedback.*', [
                ['Statistik', 'admin.statistik.index', 'admin.statistik.*'],
                ['Feedback', 'admin.feedback.index', 'admin.feedback.*'],
            ]],
        ],
        'Team' => [
            ['Aufgaben & Kalender', 'admin.aufgaben.index', 'admin.aufgaben.*|admin.kalender.*', [
                ['Aufgaben & Checkliste', 'admin.aufgaben.index', 'admin.aufgaben.*'],
                ['Kalender', 'admin.kalender.index', 'admin.kalender.*'],
            ]],
            ['Protokolle & Ablage', 'admin.protokolle.index', 'admin.protokolle.*|admin.ablage.*', [
                ['Protokolle', 'admin.protokolle.index', 'admin.protokolle.*'],
                ['Ablage', 'admin.ablage.index', 'admin.ablage.*'],
            ]],
            ['Personen', 'admin.personen.index', 'admin.personen.*|admin.vermerke.*', [
                ['Alle Personen', 'admin.personen.index', 'admin.personen.index|admin.personen.show|admin.personen.edit|admin.personen.create'],
                ['Reputation & Vermerke', 'admin.vermerke.index', 'admin.vermerke.*'],
                ['Datenschutz: Inaktive', 'admin.personen.inaktive', 'admin.personen.inaktive'],
            ]],
        ],
        'Nachrichten' => [
            ['Posteingang', 'admin.posteingang.index', 'admin.posteingang.*'],
            ['Nachricht schreiben', 'admin.rundnachricht.create', 'admin.rundnachricht.*'],
            ['Mailplan & Versand', 'admin.mailplan.index', 'admin.mailplan.*|admin.postausgang.*|admin.mailvorlagen.*', [
                ['Mailplan', 'admin.mailplan.index', 'admin.mailplan.*'],
                ['Postausgang', 'admin.postausgang.index', 'admin.postausgang.*'],
                ['Mailvorlagen', 'admin.mailvorlagen.index', 'admin.mailvorlagen.*'],
            ]],
        ],
        'Verwaltung' => [
            ['Börsen anlegen & kopieren', 'admin.boersen.index', 'admin.boersen.*'],
            ['Kategorien', 'admin.kategorien.index', 'admin.kategorien.*'],
            ['Website', 'admin.seiten.index', 'admin.seiten.*'],
            ['Team & Rechte', 'admin.team.index', 'admin.team.*', null, true],
            ['Einstellungen', 'admin.einstellungen.edit', 'admin.einstellungen.*', null, true],
            ['System & Fehler', 'admin.system.index', 'admin.system.*|admin.fehler.*', [
                ['Zustand & Updates', 'admin.system.index', 'admin.system.*'],
                ['Fehlerprotokoll', 'admin.fehler.index', 'admin.fehler.*'],
            ], true],
        ],
    ];

    $ich = auth()->user();
    $istAdmin = $ich->hasRole('admin');
    $istOrga = $ich->istOrga();
    // Für alle Team-Mitglieder (auch nur Kasse/Annahme) sichtbar
    $teamRouten = ['admin.aufgaben.index', 'admin.protokolle.index', 'admin.statistik.index'];
    $startseite = $istOrga ? route('admin.dashboard') : route('admin.aufgaben.index');
    $aktiv = fn (string $muster) => request()->routeIs(...explode('|', $muster));
    $zaehler = ['admin.posteingang.index' => $offenePost, 'admin.system.index' => $offeneFehler];

    // Reiter des aktiven Punkts (falls er mehrere Seiten bündelt)
    $reiter = collect($navigation)->flatten(1)->first(fn ($e) => ! empty($e[3]) && $aktiv($e[2]))[3] ?? null;
@endphp
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
    <title>{{ $titel ? $titel.' – ' : '' }}Klamottenbörse Orga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ menu: false }">
<x-demo-hinweis />
<div class="min-h-screen lg:flex">
    {{-- Seitenleiste --}}
    <aside class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col border-r border-stone-200 bg-white transition lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           :class="menu && 'translate-x-0'" aria-label="Hauptnavigation">
        <div class="space-y-3 border-b border-stone-200 px-4 py-4">
            <a href="{{ $startseite }}" class="block text-lg font-bold text-marke-700 no-underline">Klamottenbörse</a>
            <form method="post" action="{{ route('admin.boerse.wechseln') }}">
                @csrf
                <label for="boerse-wechsler" class="sr-only">Börse wählen</label>
                <select id="boerse-wechsler" name="boerse_id" class="feld py-1.5 text-sm font-medium" onchange="this.form.submit()">
                    @forelse ($alleBoersen as $b)
                        <option value="{{ $b->id }}" @selected($aktuelleBoerse?->id === $b->id)>{{ $b->titel }}</option>
                    @empty
                        <option>Noch keine Börse</option>
                    @endforelse
                </select>
            </form>
            @if ($istOrga)
                <x-ui.personen-auswahl :springen="true" label="" platzhalter="🔍 Person oder Nummer suchen" class="text-sm" />
            @endif
        </div>

        <nav class="flex-1 overflow-y-auto px-2 py-3 text-sm">
            @if ($istOrga)
                <a href="{{ route('admin.dashboard') }}"
                   class="flex rounded-lg px-2 py-1.5 no-underline {{ $aktiv('admin.dashboard') ? 'bg-marke-50 font-medium text-marke-800' : 'text-stone-700 hover:bg-stone-100' }}">Übersicht</a>
            @endif

            @php $mitKasse = $ich->hasAnyRole(['admin', 'orga', 'kasse']); $mitAnnahme = $ich->hasAnyRole(['admin', 'orga', 'annahme']); @endphp
            @if ($mitKasse || $mitAnnahme)
                <div class="mt-2 grid grid-cols-2 gap-2 px-1">
                    @if ($mitKasse)<a href="{{ route('kasse.index') }}" class="rounded-lg bg-marke-600 px-2 py-2 text-center font-medium text-white no-underline hover:bg-marke-700">Kasse</a>@endif
                    @if ($mitAnnahme)<a href="{{ route('tablet.index') }}" class="rounded-lg border border-marke-300 px-2 py-2 text-center font-medium text-marke-800 no-underline hover:bg-marke-50">Annahme</a>@endif
                </div>
            @endif

            @foreach ($navigation as $gruppe => $eintraege)
                @php $eintraege = array_filter($eintraege, fn ($e) => ($istAdmin || empty($e[4])) && ($istOrga || in_array($e[1], $teamRouten, true))); @endphp
                @continue(! $eintraege)
                <p class="mt-4 px-2 text-xs font-medium uppercase tracking-wide text-stone-400">{{ $gruppe }}</p>
                @foreach ($eintraege as $e)
                    @php [$text, $route, $muster] = $e; $istAktiv = $aktiv($muster); @endphp
                    <a href="{{ route($route) }}" @if ($istAktiv) aria-current="page" @endif
                       class="mt-0.5 flex items-center justify-between rounded-lg px-2 py-1.5 no-underline {{ $istAktiv ? 'bg-marke-50 font-medium text-marke-800' : 'text-stone-700 hover:bg-stone-100' }}">
                        <span>{{ $text }}</span>
                        @if (($zaehler[$route] ?? 0) > 0)
                            <x-ui.abzeichen farbe="red">{{ $zaehler[$route] }}</x-ui.abzeichen>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="border-t border-stone-200 px-4 py-3 text-sm text-stone-600">
            <a href="{{ route('admin.konto.edit') }}" class="block truncate text-stone-700 no-underline hover:underline">{{ auth()->user()->name }} · Mein Konto</a>
            <div class="flex justify-between">
                <form method="post" action="{{ route('logout') }}">@csrf<button class="text-marke-700 hover:underline">Abmelden</button></form>
                <a href="{{ url('/') }}" class="text-stone-500 no-underline hover:underline" target="_blank">Website ↗</a>
            </div>
        </div>
    </aside>
    <div class="fixed inset-0 z-20 bg-black/30 lg:hidden" x-show="menu" x-cloak @click="menu = false"></div>

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-10 flex items-center gap-3 border-b border-stone-200 bg-white px-4 py-3 lg:hidden">
            <button type="button" class="rounded-lg border border-stone-300 px-3 py-1.5" @click="menu = true" aria-label="Menü öffnen">☰</button>
            <span class="truncate font-semibold">{{ $aktuelleBoerse?->titel ?? 'Klamottenbörse' }}</span>
        </header>
        <main class="mx-auto max-w-6xl px-4 py-6 lg:px-8">
            @if ($reiter)
                <nav class="-mt-2 mb-6 flex gap-1 overflow-x-auto overflow-y-hidden border-b border-stone-200 text-sm" aria-label="Unterseiten">
                    @foreach ($reiter as [$text, $route, $muster])
                        @php $istAktiv = $aktiv($muster); @endphp
                        <a href="{{ route($route) }}" @if ($istAktiv) aria-current="page" @endif
                           class="-mb-px whitespace-nowrap border-b-2 px-3 py-2 no-underline {{ $istAktiv ? 'border-marke-600 font-medium text-marke-800' : 'border-transparent text-stone-600 hover:border-stone-300 hover:text-stone-900' }}">{{ $text }}</a>
                    @endforeach
                </nav>
            @endif
            <x-ui.flash />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
