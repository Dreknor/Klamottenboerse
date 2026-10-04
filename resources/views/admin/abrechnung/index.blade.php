@php use App\Support\Geld; @endphp
<x-layouts.admin titel="Abrechnung">
    <x-ui.kopf titel="Abrechnung" :unter="$zuletztBerechnet ? 'Zuletzt berechnet: '.$zuletztBerechnet->format('d.m.Y H:i') : 'Noch nicht berechnet'">
        <form method="post" action="{{ route('admin.abrechnung.berechnen') }}">@csrf<x-ui.knopf>Jetzt berechnen</x-ui.knopf></form>
        <x-ui.knopf art="sekundaer" :href="route('admin.abrechnung.auszahlungsplan')">Auszahlungsplan</x-ui.knopf>
        @if ($abrechnungen->isNotEmpty())
            <form method="post" action="{{ route('admin.abrechnung.freigeben') }}" onsubmit="return confirm('Ergebnis freigeben und allen Verkäufern per Mail schicken?')">
                @csrf<x-ui.knopf art="erfolg">{{ $boerse->ergebnis_freigegeben ? 'Ergebnis-Mails nachsenden' : 'Ergebnis freigeben' }}</x-ui.knopf>
            </form>
        @endif
    </x-ui.kopf>

    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <x-ui.kennzahl titel="Umsatz gesamt" :wert="Geld::format($summen['umsatz'])" :zusatz="'Kassen: '.Geld::format($bonsGesamtCent)" />
        <x-ui.kennzahl titel="Auszahlung an Verkäufer" :wert="Geld::format($summen['auszahlung'])" :zusatz="'davon offen: '.Geld::format($summen['offen'])" />
        <x-ui.kennzahl titel="Für das Kinderhaus" :wert="Geld::format($summen['gesamtKinderhaus'])" zusatz="Spende + Nr. 600 + Café" />
        <x-ui.kennzahl titel="Kuchen & Café" :wert="Geld::format($summen['sonstige'])" />
    </div>
    <p class="mt-2 text-sm text-stone-500">
        Spende der Verkäufer {{ Geld::format($summen['spende']) }} · Verkäufe Nummer {{ $boerse->kinderhaus_nummer }} (Kinderhaus, ohne Spende) {{ Geld::format($summen['kinderhaus']) }} · Café {{ Geld::format($summen['sonstige']) }}
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        <x-ui.karte titel="Je Verkäufer" class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="tabelle">
                    <thead><tr><th>Nr.</th><th>Name</th><th class="text-right">Artikel</th><th class="text-right">Umsatz</th><th class="text-right">Spende</th><th class="text-right">Auszahlung</th><th>Ausgezahlt</th></tr></thead>
                    <tbody>
                    @forelse ($abrechnungen as $a)
                        <tr>
                            <td class="font-mono font-semibold">{{ $a->teilnahme->nummer }}</td>
                            <td>{{ $a->teilnahme->anzeigeName() }}</td>
                            <td class="text-right">{{ $a->verkaufte_artikel }}</td>
                            <td class="text-right">{{ Geld::format($a->umsatz_cent) }}</td>
                            <td class="text-right">{{ Geld::format($a->spende_cent) }}</td>
                            <td class="text-right font-medium">{{ Geld::format($a->auszahlung_cent) }}</td>
                            <td>{{ $a->ausgezahlt_at?->format('H:i') ?? ($a->teilnahme->ist_kinderhaus ? '–' : '') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-stone-500">Noch keine Abrechnung. Nach Verkaufsende auf „Jetzt berechnen“ klicken.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </x-ui.karte>

        <x-ui.karte titel="Kuchen, Café und Sonstiges">
            <form method="post" action="{{ route('admin.einnahmen.store') }}" class="space-y-3">
                @csrf
                <x-ui.auswahl name="art" label="Art" :optionen="\App\Models\SonstigeEinnahme::ARTEN" />
                <x-ui.feld name="betrag" label="Betrag in €" inputmode="decimal" placeholder="z. B. 312,50" required />
                <x-ui.feld name="notiz" label="Notiz (optional)" />
                <x-ui.knopf>Erfassen</x-ui.knopf>
            </form>
            @foreach ($einnahmen as $e)
                <div class="mt-2 flex items-center justify-between border-t border-stone-100 pt-2 text-sm">
                    <span>{{ \App\Models\SonstigeEinnahme::ARTEN[$e->art] ?? $e->art }}: <strong>{{ Geld::format($e->betrag_cent) }}</strong> {{ $e->notiz }}</span>
                    <form method="post" action="{{ route('admin.einnahmen.destroy', $e) }}">@csrf @method('delete')<button class="text-xs text-red-700 hover:underline">löschen</button></form>
                </div>
            @endforeach
            <p class="mt-3 text-xs text-stone-500">Zählt nur für Statistik und Spendenbericht, nie für die Verkäuferabrechnung.</p>
        </x-ui.karte>
    </div>
</x-layouts.admin>
