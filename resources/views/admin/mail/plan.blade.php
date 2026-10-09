<x-layouts.admin titel="Mailplan">
    <x-ui.kopf titel="Mailplan" :unter="'Diese Mails gehen für '.$boerse->titel.' automatisch raus. Niemand bekommt dieselbe Mail doppelt.'" />

    <x-ui.karte>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Wann</th><th>Mail</th><th>An</th><th>Stand</th><th></th></tr></thead>
                <tbody>
                @forelse ($plan as $e)
                    @php $faellig = $e->faelligAb(); $zahlen = ($anzahlJeEintrag[$e->id] ?? collect())->pluck('anzahl', 'status'); @endphp
                    <tr class="{{ $e->aktiv ? '' : 'opacity-50' }}">
                        <td>
                            {{ $faellig?->format('d.m.Y H:i') ?? 'Datum fehlt' }}
                            <span class="block text-xs text-stone-500">{{ $e->bezugsdatum->label() }} {{ $e->versatz_tage >= 0 ? '+' : '' }}{{ $e->versatz_tage }} Tage</span>
                        </td>
                        <td><a href="{{ route('admin.mailvorlagen.edit', $e->vorlage) }}">{{ $e->vorlage->name }}</a></td>
                        <td>{{ $e->zielgruppe->label() }}</td>
                        <td>
                            @if ($e->eingeplant_at)
                                <x-ui.abzeichen farbe="emerald">verschickt</x-ui.abzeichen>
                                <span class="block text-xs text-stone-500">{{ $zahlen['versendet'] ?? 0 }} gesendet · {{ $zahlen['wartend'] ?? 0 }} wartend · {{ $zahlen['fehler'] ?? 0 }} Fehler</span>
                            @elseif (! $e->aktiv)
                                <x-ui.abzeichen>pausiert</x-ui.abzeichen>
                            @else
                                <x-ui.abzeichen farbe="sky">geplant</x-ui.abzeichen>
                            @endif
                        </td>
                        <td class="whitespace-nowrap text-right">
                            @unless ($e->eingeplant_at)
                                <form method="post" action="{{ route('admin.mailplan.umschalten', $e) }}" class="inline">@csrf<button class="text-sm text-marke-700 hover:underline">{{ $e->aktiv ? 'Pausieren' : 'Aktivieren' }}</button></form>
                                <form method="post" action="{{ route('admin.mailplan.destroy', $e) }}" class="ml-2 inline">@csrf @method('delete')<button class="text-sm text-red-700 hover:underline">Entfernen</button></form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-stone-500">Noch keine Mails geplant.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.karte>

    <x-ui.karte titel="Standard-Mailplan" class="mt-6">
        <form method="post" action="{{ route('admin.mailplan.standard') }}" class="flex flex-wrap items-center justify-between gap-3"
              onsubmit="return confirm('Standard-Mailplan wiederherstellen? Fehlende Standard-Mails werden ergänzt, noch nicht verschickte bekommen wieder ihren Standardtermin und werden aktiviert.')">
            @csrf
            <p class="text-sm text-stone-600">Ergänzt fehlende Standard-Mails und setzt noch nicht verschickte auf ihren Standardtermin zurück. Verschickte und eigene Mails bleiben unverändert.</p>
            <x-ui.knopf art="sekundaer">Standard wiederherstellen</x-ui.knopf>
        </form>
    </x-ui.karte>

    <x-ui.karte titel="Mail hinzufügen" class="mt-6">
        <form method="post" action="{{ route('admin.mailplan.store') }}" class="grid gap-3 md:grid-cols-5 md:items-end">
            @csrf
            <x-ui.auswahl name="mailvorlage_id" label="Vorlage" :optionen="$vorlagen" />
            <x-ui.auswahl name="zielgruppe" label="An" :optionen="collect(\App\Enums\Zielgruppe::cases())->mapWithKeys(fn ($z) => [$z->value => $z->label()])" />
            <x-ui.auswahl name="bezugsdatum" label="Bezogen auf" :optionen="collect(\App\Enums\Bezugsdatum::cases())->mapWithKeys(fn ($b) => [$b->value => $b->label()])" />
            <x-ui.feld name="versatz_tage" label="Tage (– vorher)" typ="number" wert="0" />
            <x-ui.knopf>Hinzufügen</x-ui.knopf>
        </form>
    </x-ui.karte>
</x-layouts.admin>
