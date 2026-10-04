<x-layouts.admin titel="Checklisten-Vorlage">
    <x-ui.kopf titel="Checklisten-Vorlage" unter="Jede neue Börse bekommt diese Aufgaben. Die Fälligkeit wird relativ zum Verkaufstag berechnet (–7 = eine Woche vorher)." />

    <x-ui.karte>
        <table class="tabelle">
            <thead><tr><th>Tage zum Verkaufstag</th><th>Aufgabe</th><th>Phase</th><th></th></tr></thead>
            <tbody>
            @foreach ($vorlage->eintraege as $e)
                <tr>
                    <td class="font-mono">{{ $e->versatz_tage > 0 ? '+' : '' }}{{ $e->versatz_tage }}</td>
                    <td>{{ $e->titel }}</td>
                    <td>{{ $e->phase }}</td>
                    <td class="text-right">
                        <form method="post" action="{{ route('admin.checklistenvorlagen.destroy', $e) }}">@csrf @method('delete')<button class="text-sm text-red-700 hover:underline">entfernen</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <form method="post" action="{{ route('admin.checklistenvorlagen.store', $vorlage) }}" class="mt-6 grid gap-3 md:grid-cols-5 md:items-end">
            @csrf
            <x-ui.feld name="versatz_tage" label="Tage (– vorher, + nachher)" typ="number" wert="-7" required />
            <x-ui.feld name="titel" label="Aufgabe" class="md:col-span-2" required />
            <x-ui.feld name="phase" label="Phase" list="phasen" />
            <datalist id="phasen">@foreach (['Planung', 'Anmeldung', 'Vorbereitung', 'Verkauf', 'Abrechnung', 'Nachbereitung'] as $p)<option value="{{ $p }}">@endforeach</datalist>
            <x-ui.knopf>Hinzufügen</x-ui.knopf>
        </form>
    </x-ui.karte>
</x-layouts.admin>
