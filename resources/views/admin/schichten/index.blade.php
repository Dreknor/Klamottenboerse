<x-layouts.admin titel="Helfer & Schichten">
    <x-ui.kopf titel="Helfer & Schichten" unter="Helfer tragen sich selbst auf der Website ein. Telefonische Zusagen hier manuell erfassen.">
        <x-ui.knopf art="sekundaer" :href="route('helfer.index')" target="_blank">Öffentliche Helferliste</x-ui.knopf>
    </x-ui.kopf>

    @forelse ($schichten as $tag => $liste)
        <h2 class="mb-3 mt-6">{{ $tag }}</h2>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($liste as $schicht)
                @php $zusagen = $schicht->einteilungen->where('status', \App\Enums\EinteilungStatus::Zugesagt); $fehlt = max(0, $schicht->soll - $zusagen->count()); @endphp
                <x-ui.karte x-data="{ neu: false }">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold">{{ $schicht->bereich }} · {{ $schicht->beginn->format('H:i') }}–{{ $schicht->ende->format('H:i') }}</p>
                            @if ($schicht->beschreibung)<p class="text-sm text-stone-500">{{ $schicht->beschreibung }}</p>@endif
                        </div>
                        <x-ui.abzeichen :farbe="$fehlt === 0 ? 'emerald' : ($fehlt >= $schicht->soll ? 'red' : 'amber')">{{ $zusagen->count() }} / {{ $schicht->soll }}</x-ui.abzeichen>
                    </div>
                    <ul class="mt-3 space-y-1 text-sm">
                        @foreach ($schicht->einteilungen as $e)
                            <li class="flex items-center justify-between {{ $e->status === \App\Enums\EinteilungStatus::Abgesagt ? 'text-stone-400 line-through' : '' }}">
                                <span>{{ $e->person->name }} <span class="text-stone-500">{{ $e->person->telefon }}</span></span>
                                <form method="post" action="{{ route('admin.einteilungen.destroy', $e) }}">@csrf @method('delete')<button class="text-xs text-red-700 hover:underline">entfernen</button></form>
                            </li>
                        @endforeach
                    </ul>
                    <button type="button" class="mt-3 text-sm text-marke-700 hover:underline" @click="neu = !neu">+ Helfer manuell eintragen</button>
                    <div x-show="neu" x-cloak class="mt-3 space-y-3 border-t border-stone-100 pt-3">
                        <form method="post" action="{{ route('admin.schichten.helfer', $schicht) }}" class="flex items-end gap-2"
                              x-data @person-gewaehlt="$nextTick(() => $el.requestSubmit())">
                            @csrf
                            <x-ui.personen-auswahl label="Bekannte Person" class="flex-1" />
                        </form>
                        <form method="post" action="{{ route('admin.schichten.helfer', $schicht) }}" class="grid grid-cols-2 gap-2">
                            @csrf
                            <input name="vorname" class="feld" placeholder="Vorname" required>
                            <input name="nachname" class="feld" placeholder="Nachname" required>
                            <input name="telefon" class="feld" placeholder="Telefon (optional)">
                            <input name="email" type="email" class="feld" placeholder="E-Mail (optional)">
                            <label class="col-span-2 flex items-center gap-2 text-sm"><input type="checkbox" name="mail" value="1"> Bestätigung per Mail (falls E-Mail angegeben)</label>
                            <div class="col-span-2"><x-ui.knopf groesse="klein">Neue Person eintragen</x-ui.knopf></div>
                        </form>
                    </div>
                    @if ($zusagen->isEmpty())
                        <form method="post" action="{{ route('admin.schichten.destroy', $schicht) }}" class="mt-2" onsubmit="return confirm('Schicht löschen?')">@csrf @method('delete')<button class="text-xs text-stone-500 hover:underline">Schicht löschen</button></form>
                    @endif
                </x-ui.karte>
            @endforeach
        </div>
    @empty
        <p class="text-stone-500">Für diese Börse gibt es noch keine Schichten.</p>
    @endforelse

    <x-ui.karte titel="Neue Schicht" class="mt-8">
        <form method="post" action="{{ route('admin.schichten.store') }}" class="grid gap-3 md:grid-cols-6 md:items-end">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium" for="bereich">Bereich</label>
                <input id="bereich" name="bereich" list="bereiche" class="feld" required>
                <datalist id="bereiche">@foreach (\App\Models\Schicht::BEREICHE as $b)<option value="{{ $b }}">@endforeach</datalist>
            </div>
            <x-ui.feld name="datum" label="Datum" typ="date" :wert="$boerse->verkaufstag->format('Y-m-d')" required />
            <x-ui.feld name="von" label="von" typ="time" required />
            <x-ui.feld name="bis" label="bis" typ="time" required />
            <x-ui.feld name="soll" label="Anzahl Helfer" typ="number" wert="2" required />
            <x-ui.feld name="beschreibung" label="Beschreibung (optional)" class="md:col-span-5" />
            <x-ui.knopf>Anlegen</x-ui.knopf>
        </form>
    </x-ui.karte>
</x-layouts.admin>
