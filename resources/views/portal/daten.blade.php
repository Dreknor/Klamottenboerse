<x-layouts.oeffentlich titel="Meine Daten">
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="flex items-center justify-between">
            <h1>Meine Daten</h1>
            <a href="{{ route('portal.index') }}">← Zurück zum Portal</a>
        </div>

        <x-ui.karte titel="Was wir über dich speichern">
            <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2">
                <dt class="text-stone-500">Name</dt><dd>{{ $person->name }}</dd>
                <dt class="text-stone-500">E-Mail</dt><dd>{{ $person->email ?? '–' }}</dd>
                <dt class="text-stone-500">Telefon</dt><dd>{{ $person->telefon ?? '–' }}</dd>
                <dt class="text-stone-500">Kinderhaus</dt><dd>{{ $person->kinderhaus_bezug->label() }}</dd>
                <dt class="text-stone-500">Info-Mails</dt><dd>{{ $person->info_mails_erlaubt_at ? 'ja' : 'nein' }}</dd>
            </dl>
            <p class="mt-4 text-sm text-stone-600">Dazu kommen deine Teilnahmen (Nummer, verkaufte Artikel, Abrechnung), Helferschichten und Mails an dich. Alles vollständig steht in der Datei zum Herunterladen.</p>
            <x-ui.knopf art="sekundaer" :href="route('portal.daten.export')" class="mt-4">Alle Daten herunterladen</x-ui.knopf>
            <p class="mt-3 text-sm text-stone-500">Etwas stimmt nicht? Schreib uns – wir korrigieren es.</p>
        </x-ui.karte>

        <x-ui.karte titel="Daten löschen">
            @if ($person->loeschung_angefragt_at)
                <p>Deine Löschung ist beantragt. Wir löschen alles, sobald die laufende Börse abgerechnet ist.</p>
            @else
                <p class="text-stone-700">Wir löschen deine Kontaktdaten, Mails, Notizen und Schichten. Verkaufszahlen bleiben ohne deinen Namen für unsere Statistik erhalten.</p>
                @if ($offen)
                    <p class="mt-2 rounded-lg bg-amber-50 p-3 text-sm text-amber-900">Du nimmst gerade an einer Börse teil. Wir löschen deine Daten dann automatisch nach der Abrechnung.</p>
                @endif
                <form method="post" action="{{ route('portal.daten.loeschen') }}" class="mt-4 space-y-3" x-data="{ ok: false }">
                    @csrf
                    <label class="flex items-start gap-2"><input type="checkbox" name="bestaetigung" value="1" class="mt-1" x-model="ok"> Ja, bitte löscht meine Daten. Das kann nicht rückgängig gemacht werden.</label>
                    <x-ui.knopf art="gefahr" x-bind:disabled="!ok">Meine Daten löschen</x-ui.knopf>
                </form>
            @endif
        </x-ui.karte>
    </div>
</x-layouts.oeffentlich>
