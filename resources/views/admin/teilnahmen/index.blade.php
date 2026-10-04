<x-layouts.admin titel="Verkäufer & Nummern">
    <x-ui.kopf titel="Verkäufer & Nummern" :unter="$boerse->belegteNummern().' von '.$boerse->kapazitaet.' Plätzen vergeben · Nummern '.$boerse->nummer_von.'–'.$boerse->nummer_bis">
        <form method="post" action="{{ route('admin.teilnahmen.nachruecken') }}">@csrf<x-ui.knopf art="sekundaer">Warteliste jetzt nachrücken</x-ui.knopf></form>
    </x-ui.kopf>

    <div class="mb-6 grid gap-6 lg:grid-cols-3">
        <x-ui.karte titel="Verkäufer manuell anmelden" class="lg:col-span-2">
            <form method="get" class="mb-3 flex gap-2">
                <input name="person_suche" value="{{ request('person_suche') }}" class="feld" placeholder="Bekannte Person suchen (Name oder E-Mail)">
                <x-ui.knopf art="sekundaer">Suchen</x-ui.knopf>
            </form>
            @foreach ($treffer as $person)
                <form method="post" action="{{ route('admin.teilnahmen.store') }}" class="flex items-center justify-between border-b border-stone-100 py-2">
                    @csrf
                    <input type="hidden" name="person_id" value="{{ $person->id }}">
                    <span>{{ $person->name }} <span class="text-sm text-stone-500">{{ $person->email }}</span></span>
                    <x-ui.knopf groesse="klein">Anmelden</x-ui.knopf>
                </form>
            @endforeach
            @if (request('person_suche') && $treffer->isEmpty())
                <p class="text-sm text-stone-500">Keine passende Person ohne Anmeldung gefunden.</p>
            @endif

            <details class="mt-3" @if ($errors->any()) open @endif>
                <summary class="cursor-pointer text-sm font-medium text-marke-700">Neue Person anlegen und anmelden</summary>
                <form method="post" action="{{ route('admin.teilnahmen.store') }}" class="mt-3 grid gap-3 md:grid-cols-2">
                    @csrf
                    <x-ui.feld name="vorname" label="Vorname" required />
                    <x-ui.feld name="nachname" label="Nachname" required />
                    <x-ui.feld name="email" label="E-Mail" typ="email" hilfe="Ohne E-Mail gehen keine automatischen Mails raus." />
                    <x-ui.feld name="telefon" label="Telefon" />
                    <x-ui.auswahl name="kinderhaus_bezug" label="Kinderhaus" :optionen="collect(\App\Enums\KinderhausBezug::cases())->mapWithKeys(fn ($b) => [$b->value => $b->label()])" />
                    <label class="flex items-center gap-2 self-end pb-2"><input type="hidden" name="mail" value="0"><input type="checkbox" name="mail" value="1" checked> Bestätigungsmail senden</label>
                    <div class="md:col-span-2"><x-ui.knopf>Anlegen und anmelden</x-ui.knopf></div>
                </form>
            </details>
        </x-ui.karte>

        <x-ui.karte titel="Nummern je 100er-Block">
            <x-nummernbloecke :boerse="$boerse" />
        </x-ui.karte>
    </div>

    <x-ui.karte>
        <form method="get" class="mb-4 flex flex-wrap gap-2">
            <input name="suche" value="{{ request('suche') }}" class="feld max-w-xs" placeholder="Nummer oder Name">
            <select name="status" class="feld max-w-xs" onchange="this.form.submit()">
                <option value="">Alle aktiven</option>
                @foreach (\App\Enums\TeilnahmeStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }} ({{ $statusAnzahl[$s->value] ?? 0 }})</option>
                @endforeach
            </select>
            <x-ui.knopf art="sekundaer">Filtern</x-ui.knopf>
        </form>

        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Nr.</th><th>Name</th><th>Status</th><th>Artikel</th><th>Kisten</th><th>Aktionen</th></tr></thead>
                <tbody>
                @forelse ($teilnahmen as $t)
                    <tr x-data="{ aendern: false, notiz: false }">
                        <td class="font-mono text-base font-semibold">{{ $t->nummer ?? ($t->wartelisten_position ? 'W'.$t->wartelisten_position : '–') }}</td>
                        <td>
                            @if ($t->person)
                                <a href="{{ route('admin.personen.show', $t->person) }}">{{ $t->person->name }}</a>
                                <span class="block text-xs text-stone-500">{{ $t->person->email ?? $t->person->telefon }}</span>
                            @else
                                <span class="font-medium">Kinderhaus</span> <span class="text-xs text-stone-500">(ohne Spende)</span>
                            @endif
                            @foreach ($t->notizen as $notiz)
                                <span class="mt-1 block rounded bg-amber-50 px-2 py-0.5 text-xs text-amber-900">✎ {{ $notiz->text }}</span>
                            @endforeach
                        </td>
                        <td>
                            <x-ui.abzeichen :farbe="$t->status->farbe()">{{ $t->status->label() }}</x-ui.abzeichen>
                            @if ($t->angebot_bis)<span class="block text-xs text-stone-500">bis {{ $t->angebot_bis->format('d.m. H:i') }}</span>@endif
                        </td>
                        <td>{{ $t->artikel_count ?: '–' }}</td>
                        <td>{{ $t->kisten_count ?: '–' }}</td>
                        <td class="whitespace-nowrap">
                            @unless ($t->ist_kinderhaus || $t->status === \App\Enums\TeilnahmeStatus::Abgesagt)
                                @if ($t->hatNummer())
                                    <button type="button" class="text-marke-700 hover:underline" @click="aendern = !aendern">Nummer ändern</button>
                                @endif
                                <form method="post" action="{{ route('admin.teilnahmen.absagen', $t) }}" class="ml-3 inline" onsubmit="return confirm('Teilnahme von {{ $t->person?->name }} wirklich absagen?')">
                                    @csrf<button class="text-red-700 hover:underline">Absagen</button>
                                </form>
                                <button type="button" class="ml-3 text-stone-600 hover:underline" @click="notiz = !notiz">Notiz</button>
                                <form x-show="notiz" x-cloak method="post" action="{{ route('admin.teilnahmen.notiz', $t) }}" class="mt-2 flex items-center gap-2">
                                    @csrf
                                    <input name="text" class="feld py-1" placeholder="z. B. bringt drei Kisten" required>
                                    <x-ui.knopf groesse="klein">Speichern</x-ui.knopf>
                                </form>
                                <form x-show="aendern" x-cloak method="post" action="{{ route('admin.teilnahmen.nummer', $t) }}" class="mt-2 flex items-center gap-2">
                                    @csrf
                                    <input name="nummer" type="number" class="feld w-24 py-1" min="{{ $boerse->nummer_von }}" max="{{ $boerse->nummer_bis }}" required>
                                    <x-ui.knopf groesse="klein">Ändern</x-ui.knopf>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-stone-500">Keine Einträge.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $teilnahmen->links() }}</div>
    </x-ui.karte>
</x-layouts.admin>
