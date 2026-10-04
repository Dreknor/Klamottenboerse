<x-layouts.admin titel="Fehler">
    <x-ui.kopf titel="Fehler #{{ $fehler->id }}" :unter="$fehler->anzahl.'× aufgetreten · zuerst '.$fehler->created_at->isoFormat('D.M.YYYY HH:mm').' · zuletzt '.$fehler->zuletzt_at?->isoFormat('D.M.YYYY HH:mm')">
        <x-ui.knopf :href="route('admin.fehler.index')" art="sekundaer">Zurück</x-ui.knopf>
        <form method="post" action="{{ route('admin.fehler.erledigt', $fehler) }}">@csrf
            <x-ui.knopf>{{ $fehler->erledigt_at ? 'Wieder öffnen' : 'Als erledigt markieren' }}</x-ui.knopf>
        </form>
    </x-ui.kopf>

    <div class="space-y-6">
        <x-ui.karte>
            <p class="mb-2"><x-ui.abzeichen :farbe="$fehler->farbe()">{{ $fehler->stufe }}</x-ui.abzeichen> <span class="font-mono text-sm">{{ $fehler->klasse }}</span></p>
            <p class="whitespace-pre-wrap break-words text-lg">{{ $fehler->nachricht }}</p>
            <dl class="mt-4 grid gap-x-6 gap-y-1 text-sm sm:grid-cols-[auto_1fr]">
                @if ($fehler->datei)<dt class="text-stone-500">Stelle</dt><dd class="font-mono break-all">{{ $fehler->datei }}:{{ $fehler->zeile }}</dd>@endif
                @if ($fehler->url)<dt class="text-stone-500">Aufruf</dt><dd class="break-all">{{ $fehler->methode }} {{ $fehler->url }}</dd>@endif
                <dt class="text-stone-500">Angemeldet</dt>
                <dd>@if ($fehler->person)<a href="{{ route('admin.personen.show', $fehler->person) }}">{{ $fehler->person->name }}</a>@else – @endif</dd>
            </dl>
        </x-ui.karte>

        @if ($fehler->kontext)
            <x-ui.karte titel="Zusatzinfos">
                <pre class="overflow-auto whitespace-pre-wrap text-xs">{{ json_encode($fehler->kontext, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            </x-ui.karte>
        @endif

        @if ($fehler->trace)
            <x-ui.karte titel="Technische Details (Stacktrace)">
                <pre class="max-h-[60vh] overflow-auto whitespace-pre text-xs leading-relaxed">{{ $fehler->trace }}</pre>
            </x-ui.karte>
        @endif
    </div>
</x-layouts.admin>
