@php use App\Support\Geld; @endphp
<x-layouts.admin titel="Auszahlungsplan">
    <x-ui.kopf titel="Auszahlungsplan mit Stückelung" :unter="$boerse->titel.' · '.$plan->zeilen->count().' Auszahlungen · '.Geld::format($plan->gesamtCent)">
        <x-ui.knopf art="sekundaer" :href="route('admin.abrechnung.index')">Zurück zur Abrechnung</x-ui.knopf>
    </x-ui.kopf>

    @if ($plan->zeilen->isEmpty())
        <x-ui.karte>
            <p>Für diese Börse gibt es noch keine Abrechnung.</p>
            @if ($vorherige)
                <p class="mt-2 text-sm text-stone-600">Tipp für die Wechselgeld-Bestellung: Wähle oben links die vorherige Börse ({{ $vorherige->titel }}) – deren Stückelung ist ein guter Anhaltspunkt.</p>
            @endif
        </x-ui.karte>
    @else
        <x-ui.karte titel="Benötigtes Geld insgesamt">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($plan->summe as $wert => $anzahl)
                    <div class="rounded-lg bg-stone-50 p-3 text-center">
                        <p class="text-2xl font-semibold">{{ $anzahl }} ×</p>
                        <p class="text-sm text-stone-600">{{ Geld::stueckBezeichnung($wert) }}</p>
                        <p class="text-xs text-stone-500">{{ Geld::format($wert * $anzahl) }}</p>
                    </div>
                @endforeach
            </div>
        </x-ui.karte>

        <x-ui.karte titel="Je Verkäufer (für die Umschläge)" class="mt-6">
            <div class="overflow-x-auto">
                <table class="tabelle">
                    <thead><tr><th>Nr.</th><th>Name</th><th class="text-right">Auszahlung</th><th>Stückelung</th></tr></thead>
                    <tbody>
                    @foreach ($plan->zeilen as $zeile)
                        <tr>
                            <td class="font-mono font-semibold">{{ $zeile['abrechnung']->teilnahme->nummer }}</td>
                            <td>{{ $zeile['abrechnung']->teilnahme->anzeigeName() }}</td>
                            <td class="text-right font-medium">{{ Geld::format($zeile['abrechnung']->auszahlung_cent) }}</td>
                            <td class="text-sm">{{ collect($zeile['stueckelung'])->map(fn ($n, $w) => $n.'× '.Geld::format($w))->implode(', ') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.karte>
    @endif
</x-layouts.admin>
