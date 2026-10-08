<x-layouts.admin titel="Kategorien">
    <x-ui.kopf titel="Kategorien" unter="Verkäufer geben bei der Anmeldung an, was sie überwiegend mitbringen. Artikel ohne Kategorie werden bei Kleidung über den Größenbereich automatisch zugeordnet." />

    @if ($boerse)
        <x-ui.karte :titel="'Angebot: '.$boerse->titel" class="mb-6">
            <p class="mb-3 text-sm text-stone-600">{{ $verkaeuferGesamt }} Verkäufer mit Nummer · {{ $ohneAngabe }} ohne Angabe</p>
            <div class="space-y-2">
                @foreach ($kategorien->where('aktiv', true) as $k)
                    @php $anzahl = $verkaeufer[$k->id] ?? 0; $anteil = $verkaeuferGesamt ? round($anzahl / $verkaeuferGesamt * 100) : 0; @endphp
                    <div class="grid grid-cols-[minmax(0,14rem)_1fr_auto] items-center gap-3 text-sm">
                        <span class="truncate">{{ $k->name }}</span>
                        <span class="h-2 rounded-full bg-stone-100"><span class="block h-2 rounded-full bg-marke-500" style="width: {{ $anteil }}%"></span></span>
                        <span class="whitespace-nowrap tabular-nums text-stone-600">{{ $anzahl }} Verk. · {{ $artikel[$k->id] ?? 0 }} Art.</span>
                    </div>
                @endforeach
            </div>
        </x-ui.karte>
    @endif

    <x-ui.karte titel="Gruppen" class="mb-6">
        <p class="mb-3 text-sm text-stone-600">Bei der Anmeldung stehen die Kategorien unter diesen Überschriften – in dieser Reihenfolge. Gibst du zwei Gruppen denselben Namen, werden sie zusammengelegt.</p>
        <form method="post" action="{{ route('admin.kategorien.gruppen') }}">
            @csrf @method('put')
            <div class="space-y-2">
                @foreach ($gruppen as $i => $gruppe)
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="gruppen[{{ $i }}][alt]" value="{{ $gruppe }}">
                        <input name="gruppen[{{ $i }}][position]" value="{{ $i + 1 }}" type="number" min="1" class="feld w-16 py-1" aria-label="Reihenfolge von {{ $gruppe }}" title="Reihenfolge">
                        <input name="gruppen[{{ $i }}][name]" value="{{ $gruppe }}" class="feld w-64 py-1" required aria-label="Name der Gruppe {{ $gruppe }}">
                        <span class="text-sm text-stone-500">{{ $kategorien->where('gruppe', $gruppe)->count() }} Kategorien</span>
                    </div>
                @endforeach
            </div>
            @error('gruppen.*.name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            <x-ui.knopf groesse="klein" class="mt-3">Gruppen speichern</x-ui.knopf>
        </form>
        <p class="mt-3 text-xs text-stone-500">Neue Gruppe: unten eine Kategorie anlegen (oder eine bestehende bearbeiten) und dabei einen neuen Gruppennamen eintippen. Eine Gruppe verschwindet, sobald keine Kategorie mehr darin ist.</p>
    </x-ui.karte>

    <x-ui.karte titel="Liste pflegen">
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Name</th><th>Gruppe</th><th>Größe von – bis</th><th>Reihenfolge</th><th>Auswählbar</th><th>Personen</th><th></th></tr></thead>
                <tbody>
                @foreach ($kategorien as $k)
                    <tr class="{{ $k->aktiv ? '' : 'text-stone-400' }}">
                        <td><input form="k{{ $k->id }}" name="name" value="{{ $k->name }}" class="feld py-1" required></td>
                        <td><input form="k{{ $k->id }}" name="gruppe" value="{{ $k->gruppe }}" class="feld w-32 py-1" list="gruppen" required></td>
                        <td class="whitespace-nowrap">
                            <input form="k{{ $k->id }}" name="groesse_von" value="{{ $k->groesse_von }}" type="number" min="0" max="999" class="feld inline-block w-20 py-1" aria-label="Größe von"> –
                            <input form="k{{ $k->id }}" name="groesse_bis" value="{{ $k->groesse_bis }}" type="number" min="0" max="999" class="feld inline-block w-20 py-1" aria-label="Größe bis">
                        </td>
                        <td><input form="k{{ $k->id }}" name="sortierung" value="{{ $k->sortierung }}" type="number" class="feld w-20 py-1" aria-label="Reihenfolge"></td>
                        <td><input form="k{{ $k->id }}" type="checkbox" name="aktiv" value="1" @checked($k->aktiv) aria-label="Bei der Anmeldung auswählbar"></td>
                        <td class="tabular-nums">{{ $k->personen_count }}</td>
                        <td class="whitespace-nowrap text-right">
                            <form id="k{{ $k->id }}" method="post" action="{{ route('admin.kategorien.update', $k) }}" class="inline">@csrf @method('put')<x-ui.knopf groesse="klein" art="sekundaer">Speichern</x-ui.knopf></form>
                            <form method="post" action="{{ route('admin.kategorien.destroy', $k) }}" class="inline" onsubmit="return confirm('Kategorie „{{ $k->name }}“ löschen? Die Angaben der Verkäufer dazu gehen verloren.')">@csrf @method('delete')<x-ui.knopf groesse="klein" art="leise">Löschen</x-ui.knopf></form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <datalist id="gruppen">@foreach ($gruppen as $g)<option value="{{ $g }}">@endforeach</datalist>

        <form method="post" action="{{ route('admin.kategorien.store') }}" class="mt-6 flex flex-wrap items-end gap-3 border-t border-stone-100 pt-4">
            @csrf
            <x-ui.feld name="name" label="Neue Kategorie" required class="w-64" />
            <x-ui.feld name="gruppe" label="Gruppe" :wert="'Weiteres'" required list="gruppen" class="w-40" />
            <x-ui.feld name="groesse_von" label="Größe von" typ="number" class="w-28" />
            <x-ui.feld name="groesse_bis" label="bis" typ="number" class="w-28" />
            <x-ui.knopf>Anlegen</x-ui.knopf>
        </form>
        <p class="mt-2 text-xs text-stone-500">Größenbereich nur bei Kleidung angeben, z. B. 74 bis 92. Nicht mehr benötigte Kategorien besser auf „nicht auswählbar“ stellen statt löschen – dann bleiben die bisherigen Angaben erhalten.</p>
    </x-ui.karte>
</x-layouts.admin>
