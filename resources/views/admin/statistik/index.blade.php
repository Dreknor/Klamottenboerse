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

        <x-ui.karte titel="Umsatz nach 100er-Block">
            @php $maxBlock = max(1, max($s['nach_block'] ?: [0])); @endphp
            @forelse ($s['nach_block'] as $block => $cent)
                <div class="mb-1.5 flex items-center gap-2 text-sm">
                    <span class="w-14 text-stone-500">{{ $block }}er</span>
                    <div class="h-4 flex-1 rounded bg-stone-100"><div class="h-4 rounded bg-sky-500" style="width: {{ $cent / $maxBlock * 100 }}%"></div></div>
                    <span class="w-24 text-right">{{ Geld::format($cent) }}</span>
                </div>
            @empty
                <p class="text-stone-500">Noch keine Verkäufe.</p>
            @endforelse
        </x-ui.karte>
    </div>

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
