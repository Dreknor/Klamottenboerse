@php use App\Support\Geld; use App\Enums\TeilnahmeStatus; @endphp
<x-layouts.oeffentlich titel="Mein Portal">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1>Hallo {{ $person->vorname }}!</h1>
        <div class="flex items-center gap-4 text-sm">
            <a href="{{ route('infoblatt') }}" target="_blank">Infoblatt (PDF)</a>
            <a href="{{ route('portal.daten') }}">Meine Daten</a>
            <form method="post" action="{{ route('portal.abmelden') }}">@csrf<button class="text-stone-600 hover:underline">Abmelden</button></form>
        </div>
    </div>

    <x-push-schalter class="mb-6" />

    @if (! $teilnahme)
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <p>Du bist für die nächste Börse noch nicht als Verkäufer angemeldet.</p>
            <x-ui.knopf :href="route('anmeldung.create')" class="mt-4">Jetzt anmelden</x-ui.knopf>
        </div>
    @else
        @php $b = $teilnahme->boerse; @endphp
        <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-stone-500">{{ $b->titel }} · {{ $b->verkaufstag->isoFormat('dddd, D. MMMM YYYY') }}</p>
                    @if ($teilnahme->hatNummer())
                        <p class="mt-1 text-lg">Deine Verkäufernummer</p>
                        <p class="text-5xl font-bold text-marke-700">{{ $teilnahme->nummer }}</p>
                    @else
                        <p class="mt-1 text-lg font-semibold">{{ $teilnahme->status->label() }}</p>
                        @if ($teilnahme->status === TeilnahmeStatus::Warteliste)
                            <p class="text-stone-600">Platz {{ $teilnahme->wartelisten_position }} – wir melden uns automatisch, sobald ein Platz frei wird.</p>
                        @endif
                    @endif
                </div>
                <x-ui.abzeichen :farbe="$teilnahme->status->farbe()" class="text-sm">{{ $teilnahme->status->label() }}</x-ui.abzeichen>
            </div>

            @if ($teilnahme->hatNummer())
                <dl class="mt-6 grid gap-3 text-sm sm:grid-cols-3">
                    <div><dt class="text-stone-500">Kiste abgeben</dt><dd class="font-medium">{{ $b->anlieferung_beginn?->isoFormat('ddd, D. MMM, H:mm') }}–{{ $b->anlieferung_ende?->format('H:i') }} Uhr</dd></div>
                    <div><dt class="text-stone-500">Abholen</dt><dd class="font-medium">{{ $b->abholung_beginn?->isoFormat('ddd, D. MMM, H:mm') }}–{{ $b->abholung_ende?->format('H:i') }} Uhr</dd></div>
                    <div><dt class="text-stone-500">Ort</dt><dd class="font-medium">{{ $b->ort?->name }}<br>{{ $b->ort?->adresse }}</dd></div>
                </dl>
            @endif
        </div>

        {{-- Ergebnis / Live-Erlös --}}
        @if ($teilnahme->abrechnung && $b->ergebnis_freigegeben)
            <div class="mt-6 rounded-2xl bg-emerald-50 p-6 ring-1 ring-emerald-200">
                <h2>Dein Ergebnis</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div><dt class="text-sm text-stone-600">Verkauft</dt><dd class="text-xl font-semibold">{{ $teilnahme->abrechnung->verkaufte_artikel }} Teile</dd></div>
                    <div><dt class="text-sm text-stone-600">Umsatz</dt><dd class="text-xl font-semibold">{{ Geld::format($teilnahme->abrechnung->umsatz_cent) }}</dd></div>
                    <div><dt class="text-sm text-stone-600">Spende</dt><dd class="text-xl font-semibold">{{ Geld::format($teilnahme->abrechnung->spende_cent) }}</dd></div>
                    <div><dt class="text-sm text-stone-600">Auszahlung</dt><dd class="text-xl font-semibold text-emerald-800">{{ Geld::format($teilnahme->abrechnung->auszahlung_cent) }}</dd></div>
                </dl>
            </div>
        @elseif ($b->live_erloes_freigegeben && $verkauft->isNotEmpty())
            <div class="mt-6 rounded-2xl bg-sky-50 p-6 ring-1 ring-sky-200">
                <h2>Live: bisher verkauft</h2>
                <p class="mt-2 text-2xl font-semibold">{{ $verkauft->count() }} Teile · {{ Geld::format($verkauft->sum('preis_cent')) }}</p>
                <p class="text-sm text-stone-600">vor Abzug der Spende</p>
            </div>
        @endif

        {{-- Artikel und Etiketten --}}
        @if ($teilnahme->hatNummer())
            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2>Artikel und Etiketten <span class="text-sm font-normal text-stone-500">(freiwillig)</span></h2>
                        <p class="text-sm text-stone-600">Handschriftliche Etiketten sind genauso in Ordnung. Wer hier erfasst, bekommt Etiketten mit QR-Code – das geht an der Kasse schneller.
                            @if ($b->max_teile) Maximal {{ $b->max_teile }} Teile. @endif</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($teilnahme->artikel->isNotEmpty())
                            <x-ui.knopf art="sekundaer" :href="route('portal.etiketten')" target="_blank">Etiketten drucken (PDF)</x-ui.knopf>
                        @endif
                        <x-ui.knopf art="sekundaer" :href="route('portal.kistenzettel')" target="_blank">Kistenzettel drucken</x-ui.knopf>
                    </div>
                </div>

                @if ($teilnahme->status === TeilnahmeStatus::Zugeteilt)
                    <form method="post" action="{{ route('portal.artikel.store') }}" class="mt-4 grid gap-3 rounded-xl bg-stone-50 p-4 sm:grid-cols-6 sm:items-end">
                        @csrf
                        <x-ui.feld name="beschreibung" label="Was?" class="sm:col-span-2" placeholder="z. B. Regenjacke blau" required />
                        <x-ui.auswahl name="kategorie_id" label="Kategorie" :optionen="$kategorien" leer="–" />
                        <x-ui.feld name="groesse" label="Größe" placeholder="z. B. 110" />
                        <x-ui.feld name="preis" label="Preis €" inputmode="decimal" placeholder="4,50" required />
                        <x-ui.feld name="anzahl" label="Stück" typ="number" wert="1" min="1" max="20" />
                        <div class="sm:col-span-6"><x-ui.knopf>Hinzufügen</x-ui.knopf></div>
                    </form>
                @endif

                @if ($teilnahme->artikel->isNotEmpty())
                    @php $verkaufteNummern = $verkauft->pluck('artikelnummer')->flip(); @endphp
                    <div class="mt-4 overflow-x-auto">
                        <table class="tabelle">
                            <thead><tr><th>Nr.</th><th>Artikel</th><th>Größe</th><th class="text-right">Preis</th><th></th></tr></thead>
                            <tbody>
                            @foreach ($teilnahme->artikel as $a)
                                <tr>
                                    <td class="font-mono">{{ $teilnahme->nummer }}-{{ $a->laufnummer }}</td>
                                    <td>{{ $a->beschreibung }} <span class="text-xs text-stone-500">{{ $a->kategorie?->name }}</span></td>
                                    <td>{{ $a->groesse }}</td>
                                    <td class="text-right">{{ $a->preis() }}</td>
                                    <td class="text-right">
                                        @if ($verkaufteNummern->has($a->laufnummer))
                                            <x-ui.abzeichen farbe="emerald">verkauft</x-ui.abzeichen>
                                        @elseif ($teilnahme->status === TeilnahmeStatus::Zugeteilt)
                                            <form method="post" action="{{ route('portal.artikel.destroy', $a) }}">@csrf @method('delete')<button class="text-xs text-red-700 hover:underline">entfernen</button></form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot><tr><td colspan="3" class="font-medium">{{ $teilnahme->artikel->count() }} Artikel</td><td class="text-right font-medium">{{ Geld::format($teilnahme->artikel->sum('preis_cent')) }}</td><td></td></tr></tfoot>
                        </table>
                    </div>
                @endif
            </div>
        @endif

        @if (in_array($teilnahme->status, [TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Warteliste, TeilnahmeStatus::Angeboten], true))
            <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200" x-data="{ sicher: false }">
                <h2>Doch keine Zeit?</h2>
                <p class="mt-1 text-sm text-stone-600">Bitte sag ab, damit jemand von der Warteliste nachrücken kann.</p>
                <button type="button" class="mt-3 text-red-700 hover:underline" @click="sicher = true" x-show="!sicher">Teilnahme absagen …</button>
                <form x-show="sicher" x-cloak method="post" action="{{ route('portal.absage') }}" class="mt-3">
                    @csrf<x-ui.knopf art="gefahr">Ja, ich sage ab</x-ui.knopf>
                </form>
            </div>
        @endif
    @endif

    @if ($schichten->isNotEmpty())
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Deine Helferschichten</h2>
            @foreach ($schichten as $e)
                <p class="mt-2">{{ $e->schicht->bereich }}: {{ $e->schicht->beginn->isoFormat('dddd, D. MMMM, H:mm') }}–{{ $e->schicht->ende->format('H:i') }} Uhr
                    · <a href="{{ \App\Domain\Teilnahme\Links::helferAbsage($e) }}" class="text-sm">absagen</a></p>
            @endforeach
        </div>
    @endif

    @if ($unterlagen->isNotEmpty())
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Unterlagen für Helfer</h2>
            @foreach ($unterlagen as $ordner)
                <p class="mt-3 font-medium">{{ $ordner->name }}</p>
                @forelse ($ordner->media as $m)
                    <a href="{{ route('portal.unterlage', $m) }}" target="_blank" class="block py-1 text-sm">{{ str_starts_with($m->mime_type, 'image/') ? '🖼' : '📄' }} {{ $m->file_name }}</a>
                @empty
                    <p class="text-sm text-stone-500">Noch nichts hinterlegt.</p>
                @endforelse
            @endforeach
        </div>
    @endif

    @if ($fruehere->isNotEmpty())
        <div class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
            <h2>Frühere Börsen</h2>
            @foreach ($fruehere as $t)
                <p class="mt-2 text-sm">{{ $t->boerse->titel }}: Nummer {{ $t->nummer }}, {{ $t->abrechnung->verkaufte_artikel }} Teile, Auszahlung {{ Geld::format($t->abrechnung->auszahlung_cent) }}</p>
            @endforeach
        </div>
    @endif
</x-layouts.oeffentlich>
