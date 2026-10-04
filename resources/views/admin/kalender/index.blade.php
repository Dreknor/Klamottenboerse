@php
    $farben = ['termin' => 'bg-sky-100 text-sky-900', 'boerse' => 'bg-marke-100 text-marke-800', 'schicht' => 'bg-emerald-100 text-emerald-900', 'aufgabe' => 'bg-amber-100 text-amber-900'];
@endphp
<x-layouts.admin titel="Kalender">
    <x-ui.kopf titel="Teamkalender" :unter="$monat->isoFormat('MMMM YYYY')">
        <x-ui.knopf art="sekundaer" :href="route('admin.kalender.index', ['monat' => $monat->subMonth()->format('Y-m')])">← Vormonat</x-ui.knopf>
        <x-ui.knopf art="sekundaer" :href="route('admin.kalender.index')">Heute</x-ui.knopf>
        <x-ui.knopf art="sekundaer" :href="route('admin.kalender.index', ['monat' => $monat->addMonth()->format('Y-m')])">Nächster →</x-ui.knopf>
    </x-ui.kopf>

    <div class="mb-3 flex flex-wrap gap-2 text-xs">
        <span class="rounded px-2 py-0.5 {{ $farben['termin'] }}">Team-Termin</span>
        <span class="rounded px-2 py-0.5 {{ $farben['boerse'] }}">Börse</span>
        <span class="rounded px-2 py-0.5 {{ $farben['schicht'] }}">Schichten</span>
        <span class="rounded px-2 py-0.5 {{ $farben['aufgabe'] }}">Aufgabe fällig</span>
    </div>

    <div class="overflow-x-auto rounded-xl border border-stone-200 bg-white">
        <div class="grid min-w-[700px] grid-cols-7 text-sm">
            @foreach (['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'] as $tag)
                <div class="border-b border-stone-200 px-2 py-1 font-medium text-stone-500">{{ $tag }}</div>
            @endforeach
            @for ($tag = $von; $tag->lte($bis); $tag = $tag->addDay())
                <div class="min-h-24 border-b border-r border-stone-100 p-1 {{ $tag->month !== $monat->month ? 'bg-stone-50 text-stone-400' : '' }}">
                    <div class="mb-1 text-xs {{ $tag->isToday() ? 'inline-block rounded-full bg-marke-600 px-1.5 text-white' : '' }}">{{ $tag->day }}</div>
                    @foreach ($eintraege[$tag->toDateString()] ?? [] as $e)
                        <div class="mb-0.5 truncate rounded px-1 text-xs {{ $farben[$e['art']] }}" title="{{ $e['titel'] }}">{{ $e['titel'] }}</div>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>

    <x-ui.karte titel="Team-Termin eintragen" class="mt-6">
        <form method="post" action="{{ route('admin.termine.store') }}" class="grid gap-3 md:grid-cols-4 md:items-end">
            @csrf
            <x-ui.feld name="titel" label="Titel" class="md:col-span-2" required hilfe="z. B. Orga-Treffen, Saal-Übergabe" />
            <x-ui.feld name="beginn" label="Beginn" typ="datetime-local" required />
            <x-ui.feld name="ende" label="Ende" typ="datetime-local" />
            <x-ui.feld name="ort" label="Ort" class="md:col-span-2" />
            <x-ui.feld name="beschreibung" label="Notiz" class="md:col-span-2" />
            <x-ui.knopf>Eintragen</x-ui.knopf>
        </form>
        @php $termine = collect($eintraege)->flatten(1)->where('art', 'termin'); @endphp
        @if ($termine->isNotEmpty())
            <h3 class="mb-2 mt-6 font-medium">Termine in diesem Monat</h3>
            @foreach ($termine as $e)
                <div class="flex items-center justify-between border-b border-stone-100 py-1.5 text-sm">
                    <span>{{ $e['termin']->beginn->format('d.m. H:i') }} – {{ $e['termin']->titel }} {{ $e['termin']->ort ? '('.$e['termin']->ort.')' : '' }}</span>
                    <form method="post" action="{{ route('admin.termine.destroy', $e['termin']) }}">@csrf @method('delete')<button class="text-xs text-red-700 hover:underline">löschen</button></form>
                </div>
            @endforeach
        @endif
    </x-ui.karte>
</x-layouts.admin>
