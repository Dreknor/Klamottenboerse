@php use App\Support\Geld; $s = $statistik; @endphp
<x-layouts.admin titel="Statistik">
    <x-ui.kopf titel="Statistik" :unter="$boerse->titel" />

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-ui.kennzahl titel="Umsatz" :wert="Geld::format($s['umsatz'])" :zusatz="$s['bons'].' Einkäufe, Ø '.Geld::format($s['durchschnitt_bon'])" />
        <x-ui.kennzahl titel="Verkaufte Artikel" :wert="number_format($s['artikel_verkauft'], 0, ',', '.')" :zusatz="$s['verkaufsquote'] !== null ? 'Verkaufsquote erfasster Artikel: '.number_format($s['verkaufsquote'], 1, ',', '').' %' : null" />
        <x-ui.kennzahl titel="Verkäufer" :wert="$s['verkaeufer']" :zusatz="$s['warteliste'].' Warteliste · '.$s['absagen'].' Absagen · Ø '.Geld::format($s['durchschnitt_verkaeufer'])" />
        <x-ui.kennzahl titel="Für das Kinderhaus" :wert="Geld::format($s['spende'] + $s['kinderhaus_umsatz'] + $s['cafe'])" :zusatz="'Spende '.Geld::format($s['spende']).' · Nr. 600 '.Geld::format($s['kinderhaus_umsatz']).' · Café '.Geld::format($s['cafe'])" />
        <x-ui.kennzahl titel="Helfer" :wert="$s['helfer']" />
        <x-ui.kennzahl titel="Feedback" :wert="$s['feedback_schnitt'] ? number_format($s['feedback_schnitt'], 1, ',', '').' / 5' : '–'" :zusatz="$s['feedback_anzahl'].' Antworten'" :href="route('admin.feedback.index')" />
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-ui.karte titel="Umsatz nach Uhrzeit">
            @php $maxStunde = max(1, max($s['nach_stunde'] ?: [0])); @endphp
            @forelse ($s['nach_stunde'] as $stunde => $cent)
                <div class="mb-1.5 flex items-center gap-2 text-sm">
                    <span class="w-14 text-stone-500">{{ $stunde }}–{{ $stunde + 1 }} Uhr</span>
                    <div class="h-4 flex-1 rounded bg-stone-100"><div class="h-4 rounded bg-marke-500" style="width: {{ $cent / $maxStunde * 100 }}%"></div></div>
                    <span class="w-24 text-right">{{ Geld::format($cent) }}</span>
                </div>
            @empty
                <p class="text-stone-500">Noch keine Verkäufe.</p>
            @endforelse
        </x-ui.karte>

        <x-ui.karte titel="Umsatz nach Größe">
            @php $maxGroesse = max(1, max(array_column($s['nach_groesse'], 'umsatz') ?: [0])); @endphp
            @forelse ($s['nach_groesse'] as $groesse => $g)
                <div class="mb-1.5 flex items-center gap-2 text-sm">
                    <span class="w-16 truncate text-stone-500" title="{{ $groesse }}">{{ $groesse }}</span>
                    <div class="h-4 flex-1 rounded bg-stone-100"><div class="h-4 rounded bg-sky-500" style="width: {{ $g['umsatz'] / $maxGroesse * 100 }}%"></div></div>
                    <span class="w-16 text-right text-xs text-stone-500">{{ $g['verkauft'] }} Stk.</span>
                    <span class="w-24 text-right">{{ Geld::format($g['umsatz']) }}</span>
                </div>
            @empty
                <p class="text-stone-500">Noch keine verkauften Artikel mit Größenangabe.</p>
            @endforelse
        </x-ui.karte>
    </div>

    <x-ui.karte titel="Umsatz nach Kategorie" class="mt-6">
        @php $maxKategorie = max(1, max(array_column($s['nach_kategorie'], 'umsatz') ?: [0])); @endphp
        @if ($s['nach_kategorie'])
            <div class="overflow-x-auto">
                <table class="tabelle">
                    <thead><tr><th>Kategorie</th><th>Umsatz</th><th class="text-right">Verkauft</th><th class="text-right">Erfasst</th><th class="text-right">Quote</th><th class="text-right">Ø Preis</th></tr></thead>
                    <tbody>
                    @foreach ($s['nach_kategorie'] as $k)
                        <tr>
                            <td>{{ $k['name'] }}@if ($k['gruppe'])<span class="ml-1 text-xs text-stone-400">{{ $k['gruppe'] }}</span>@endif</td>
                            <td class="w-1/3">
                                <div class="flex items-center gap-2">
                                    <div class="h-3 rounded bg-sky-500" style="width: {{ $k['umsatz'] / $maxKategorie * 100 }}%"></div>
                                    <span class="whitespace-nowrap text-xs">{{ Geld::format($k['umsatz']) }}</span>
                                </div>
                            </td>
                            <td class="text-right">{{ number_format($k['verkauft'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ $k['erfasst'] ? number_format($k['erfasst'], 0, ',', '.') : '–' }}</td>
                            <td class="text-right">{{ $k['quote'] !== null ? number_format($k['quote'], 1, ',', '').' %' : '–' }}</td>
                            <td class="text-right">{{ $k['verkauft'] ? Geld::format(intdiv($k['umsatz'], $k['verkauft'])) : '–' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-stone-500">Quote = Anteil der erfassten Artikel, die verkauft wurden. „Ohne erfasstes Etikett“: Verkäufe, die an der Kasse von Hand eingegeben wurden und zu keinem erfassten Artikel passen.</p>
        @else
            <p class="text-stone-500">Noch keine Verkäufe.</p>
        @endif
    </x-ui.karte>

    <x-ui.karte titel="Vergleich aller Börsen" class="mt-6">
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Börse</th><th>Umsatz</th><th class="text-right">Verkäufer</th><th class="text-right">Artikel</th><th class="text-right">Einkäufe</th><th class="text-right">Ø Einkauf</th><th class="text-right">Spende</th><th class="text-right">Café</th></tr></thead>
                <tbody>
                @foreach ($vergleich->reverse() as $z)
                    <tr class="{{ $z['boerse']->is($boerse) ? 'bg-marke-50' : '' }}">
                        <td class="whitespace-nowrap">{{ $z['boerse']->verkaufstag->format('m/Y') }}</td>
                        <td class="w-1/3">
                            <div class="flex items-center gap-2">
                                <div class="h-3 rounded bg-marke-500" style="width: {{ $z['umsatz'] / $maxUmsatz * 100 }}%"></div>
                                <span class="whitespace-nowrap text-xs">{{ Geld::format($z['umsatz']) }}</span>
                            </div>
                        </td>
                        <td class="text-right">{{ $z['verkaeufer'] }}</td>
                        <td class="text-right">{{ number_format($z['artikel_verkauft'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ $z['bons'] }}</td>
                        <td class="text-right">{{ Geld::format($z['durchschnitt_bon']) }}</td>
                        <td class="text-right">{{ Geld::format($z['spende']) }}</td>
                        <td class="text-right">{{ $z['cafe'] ? Geld::format($z['cafe']) : '–' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.karte>
</x-layouts.admin>
