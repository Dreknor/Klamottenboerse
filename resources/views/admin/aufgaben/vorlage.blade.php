<x-layouts.admin titel="Checklisten-Vorlagen">
    <x-ui.kopf titel="Checklisten-Vorlagen" unter="Die Standard-Checkliste bekommt jede neue Börse automatisch. Weitere Vorlagen (z. B. „Aufbau Kasse“) wendest du bei Bedarf auf die aktuelle Börse an. Die Fälligkeit zählt ab dem Verkaufstag (–7 = eine Woche vorher)." />

    <div class="grid gap-6 lg:grid-cols-4">
        <div class="space-y-3">
            @foreach ($vorlagen as $v)
                <a href="{{ route('admin.checklistenvorlagen.index', ['vorlage' => $v->id]) }}"
                   class="block rounded-xl px-4 py-3 no-underline ring-1 {{ $v->id === $vorlage->id ? 'bg-marke-50 ring-marke-200 text-marke-800' : 'bg-white ring-stone-200 text-stone-800' }}">
                    <span class="font-medium">{{ $v->name }}</span>
                    <span class="block text-xs text-stone-500">{{ $v->eintraege_count }} Einträge{{ $v->fuer_neue_boersen ? ' · Standard für neue Börsen' : '' }}</span>
                </a>
            @endforeach
            <form method="post" action="{{ route('admin.checklistenvorlagen.anlegen') }}" class="space-y-2 rounded-xl bg-white p-4 ring-1 ring-stone-200">
                @csrf
                <input name="name" class="feld" placeholder="Neue Vorlage, z. B. Aufbau Kasse" required>
                <x-ui.knopf groesse="klein" art="sekundaer">Vorlage anlegen</x-ui.knopf>
            </form>
        </div>

        <x-ui.karte :titel="$vorlage->name" class="lg:col-span-3">
            <x-slot:aktionen>
                @unless ($vorlage->fuer_neue_boersen)
                    <form method="post" action="{{ route('admin.checklistenvorlagen.anwenden', $vorlage) }}">@csrf<x-ui.knopf groesse="klein">Auf aktuelle Börse anwenden</x-ui.knopf></form>
                    <form method="post" action="{{ route('admin.checklistenvorlagen.loeschen', $vorlage) }}" onsubmit="return confirm('Vorlage löschen?')">@csrf @method('delete')<x-ui.knopf groesse="klein" art="leise">Löschen</x-ui.knopf></form>
                @endunless
            </x-slot:aktionen>

            <table class="tabelle">
                <thead><tr><th>Tage zum Verkaufstag</th><th>Aufgabe</th><th>Phase</th><th></th></tr></thead>
                <tbody>
                @forelse ($vorlage->eintraege as $e)
                    <tr>
                        <td class="font-mono">{{ $e->versatz_tage > 0 ? '+' : '' }}{{ $e->versatz_tage }}</td>
                        <td>{{ $e->titel }}</td>
                        <td>{{ $e->phase }}</td>
                        <td class="text-right">
                            <form method="post" action="{{ route('admin.checklistenvorlagen.destroy', $e) }}">@csrf @method('delete')<button class="text-sm text-red-700 hover:underline">entfernen</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-stone-500">Noch keine Einträge.</td></tr>
                @endforelse
                </tbody>
            </table>

            <form method="post" action="{{ route('admin.checklistenvorlagen.store', $vorlage) }}" class="mt-6 grid gap-3 md:grid-cols-5 md:items-end">
                @csrf
                <x-ui.feld name="versatz_tage" label="Tage (– vorher, + nachher)" typ="number" wert="0" required />
                <x-ui.feld name="titel" label="Aufgabe" class="md:col-span-2" required />
                <x-ui.feld name="phase" label="Phase" list="phasen" />
                <datalist id="phasen">@foreach (['Planung', 'Anmeldung', 'Vorbereitung', 'Aufbau', 'Verkauf', 'Abbau', 'Abrechnung', 'Nachbereitung'] as $p)<option value="{{ $p }}">@endforeach</datalist>
                <x-ui.knopf>Hinzufügen</x-ui.knopf>
            </form>
        </x-ui.karte>
    </div>
</x-layouts.admin>
