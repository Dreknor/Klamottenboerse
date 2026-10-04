<x-layouts.admin titel="Übersicht">
    <x-ui.kopf :titel="$boerse->titel" :unter="$boerse->verkaufstag->isoFormat('dddd, D. MMMM YYYY').' · Phase: '.$phase->label()">
        <x-ui.knopf art="sekundaer" :href="route('admin.boersen.edit', $boerse)">Börse bearbeiten</x-ui.knopf>
    </x-ui.kopf>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-ui.kennzahl titel="Verkäufer" :wert="$belegt.' / '.$boerse->kapazitaet" :zusatz="$warteliste.' auf der Warteliste'" :href="route('admin.teilnahmen.index')" />
        <x-ui.kennzahl titel="Schichten besetzt" :wert="$schichtenBesetzt.' / '.$schichtenSoll" :href="route('admin.schichten.index')" />
        <x-ui.kennzahl titel="Artikel im Portal erfasst" :wert="number_format($artikelErfasst, 0, ',', '.')" zusatz="freiwillig" />
        <x-ui.kennzahl titel="Umsatz Kasse" :wert="\App\Support\Geld::format($umsatzCent)" :href="route('admin.abrechnung.index')" />
    </div>

    @if ($posteingangOffen || $mailFehler)
        <div class="mt-4 flex flex-wrap gap-3">
            @if ($posteingangOffen)
                <a href="{{ route('admin.posteingang.index') }}" class="rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-amber-900 no-underline">✉ {{ $posteingangOffen }} offene Mail(s) im Posteingang</a>
            @endif
            @if ($mailFehler)
                <a href="{{ route('admin.postausgang.index', ['status' => 'fehler']) }}" class="rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-red-900 no-underline">⚠ {{ $mailFehler }} Mail(s) konnten nicht zugestellt werden</a>
            @endif
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.karte titel="Checkliste" class="lg:col-span-2">
            <x-slot:aktionen>
                <span class="text-sm text-stone-500">{{ $aufgabenErledigt }} erledigt</span>
                <x-ui.knopf art="sekundaer" groesse="klein" :href="route('admin.aufgaben.index')">Alle Aufgaben</x-ui.knopf>
            </x-slot:aktionen>
            @forelse ($aufgabenOffen->take(10) as $aufgabe)
                <div class="flex items-start gap-3 border-b border-stone-100 py-2 last:border-0">
                    <form method="post" action="{{ route('admin.aufgaben.erledigt', $aufgabe) }}">
                        @csrf
                        <button class="mt-0.5 h-5 w-5 rounded border border-stone-400 hover:bg-emerald-100" title="Als erledigt markieren" aria-label="{{ $aufgabe->titel }} erledigt"></button>
                    </form>
                    <div class="flex-1">
                        <p class="{{ $aufgabe->istUeberfaellig() ? 'font-medium text-red-800' : '' }}">{{ $aufgabe->titel }}</p>
                        <p class="text-xs text-stone-500">
                            {{ $aufgabe->phase }}
                            @if ($aufgabe->faellig_am) · fällig {{ $aufgabe->faellig_am->format('d.m.Y') }} @endif
                            @if ($aufgabe->istUeberfaellig()) · <span class="text-red-700">überfällig</span> @endif
                            @if ($aufgabe->zustaendig) · {{ $aufgabe->zustaendig->name }} @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-stone-500">Alle Aufgaben erledigt. 🎉</p>
            @endforelse
        </x-ui.karte>

        <x-ui.karte titel="Nummern je 100er-Block">
            @php $ziel = $boerse->zielProBlock(); @endphp
            <p class="mb-3 text-sm text-stone-500">Ziel: {{ $ziel }} je Block (± {{ $boerse->block_toleranz }})</p>
            @foreach ($blockbelegung as $start => $anzahl)
                @php
                    $abweichung = abs($anzahl - $ziel);
                    $farbe = $abweichung <= $boerse->block_toleranz ? 'bg-emerald-500' : 'bg-amber-500';
                @endphp
                <div class="mb-2">
                    <div class="flex justify-between text-sm"><span>{{ $start }}er</span><span>{{ $anzahl }}</span></div>
                    <div class="h-2 rounded bg-stone-100"><div class="h-2 rounded {{ $farbe }}" style="width: {{ min(100, $ziel ? $anzahl / $ziel * 100 : 0) }}%"></div></div>
                </div>
            @endforeach
            <p class="mt-3 text-sm text-stone-500">Nummer {{ $boerse->kinderhaus_nummer }}: Kinderhaus (fest, ohne Spende)</p>
        </x-ui.karte>
    </div>

    @if ($mailWartend)
        <p class="mt-6 text-sm text-stone-500">{{ $mailWartend }} Mail(s) warten auf Versand (gedrosselt auf {{ \App\Support\Einstellungen::get('mail_max_pro_stunde') }} pro Stunde).</p>
    @endif
</x-layouts.admin>
