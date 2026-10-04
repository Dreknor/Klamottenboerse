<x-layouts.admin titel="Reservierungen">
    <x-ui.kopf titel="Fest reservierte Nummern" unter="Reservierte Nummern bekommt nur die jeweilige Person. „Nur diesmal freigeben“ gibt die Nummer für die aktuelle Börse frei (z. B. wenn jemand nicht kommt) – bei einer Absage passiert das automatisch." />

    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.karte titel="Neue Reservierung">
            <form method="post" action="{{ route('admin.reservierungen.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="person_id" class="mb-1 block text-sm font-medium">Person</label>
                    <select id="person_id" name="person_id" class="feld" required>
                        <option value="">Bitte wählen …</option>
                        @foreach ($personen as $p)
                            <option value="{{ $p->id }}">{{ $p->nachname }}, {{ $p->vorname }} {{ $p->email ? '('.$p->email.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <x-ui.feld name="nummer" label="Nummer" typ="number" required />
                <x-ui.feld name="grund" label="Grund (optional)" hilfe="z. B. Orga-Team, langjährige Helferin" />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="dauerhaft" value="1" checked> Dauerhaft (für alle künftigen Börsen)</label>
                <x-ui.knopf>Reservieren</x-ui.knopf>
            </form>
        </x-ui.karte>

        <x-ui.karte titel="Reservierungen" class="lg:col-span-2">
            <table class="tabelle">
                <thead><tr><th>Nr.</th><th>Person</th><th>Gilt für</th><th>Grund</th><th></th></tr></thead>
                <tbody>
                @if ($boerse)
                    <tr class="bg-stone-50"><td class="font-mono font-semibold">{{ $boerse->kinderhaus_nummer }}</td><td>Kinderhaus</td><td>immer</td><td>feste Nummer, ohne Spende</td><td></td></tr>
                @endif
                @forelse ($reservierungen as $r)
                    @php $frei = $boerse && $r->istFreigegebenFuer($boerse); @endphp
                    <tr class="{{ $frei ? 'bg-amber-50' : '' }}">
                        <td class="font-mono font-semibold">{{ $r->nummer }}</td>
                        <td><a href="{{ route('admin.personen.show', $r->person) }}">{{ $r->person->name }}</a></td>
                        <td>
                            {{ $r->boerse?->titel ?? 'dauerhaft' }}
                            @if ($frei)<span class="block text-xs text-amber-800">für {{ $boerse->titel }} freigegeben ({{ $r->freigaben->firstWhere('id', $boerse->id)->pivot->grund }})</span>@endif
                        </td>
                        <td>{{ $r->grund }}</td>
                        <td class="whitespace-nowrap text-right">
                            @if ($boerse && $r->boerse_id === null)
                                @if ($frei)
                                    <form method="post" action="{{ route('admin.reservierungen.freigabe-zuruecknehmen', $r) }}" class="inline">
                                        @csrf @method('delete')<button class="text-sm text-marke-700 hover:underline">Wieder reservieren</button>
                                    </form>
                                @else
                                    <form method="post" action="{{ route('admin.reservierungen.freigeben', $r) }}" class="inline" onsubmit="return confirm('Nummer {{ $r->nummer }} nur für {{ $boerse->titel }} freigeben? Die Reservierung bleibt für spätere Börsen bestehen.')">
                                        @csrf<button class="text-sm text-marke-700 hover:underline">Nur diesmal freigeben</button>
                                    </form>
                                @endif
                            @endif
                            <form method="post" action="{{ route('admin.reservierungen.destroy', $r) }}" class="ml-3 inline" onsubmit="return confirm('Reservierung der Nummer {{ $r->nummer }} für {{ $r->person->name }} ganz aufheben?')">
                                @csrf @method('delete')<button class="text-sm text-red-700 hover:underline">Ganz aufheben</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-stone-500">Keine weiteren Reservierungen.</td></tr>
                @endforelse
                </tbody>
            </table>
        </x-ui.karte>
    </div>
</x-layouts.admin>
