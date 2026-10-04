<x-layouts.admin titel="Einstellungen">
    <x-ui.kopf titel="Einstellungen" />

    <form method="post" action="{{ route('admin.einstellungen.update') }}" class="space-y-6">
        @csrf @method('put')
        <x-ui.karte titel="Allgemein">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.feld name="vereinsname" label="Name der Klamottenbörse" :wert="$werte['vereinsname']" required />
                <x-ui.feld name="empfaenger_spende" label="Empfänger der Spende" :wert="$werte['empfaenger_spende']" required />
            </div>
        </x-ui.karte>

        <x-ui.karte titel="Mailversand">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.feld name="mail_max_pro_stunde" label="Höchstens Mails pro Stunde" typ="number" :wert="$werte['mail_max_pro_stunde']" required
                           hilfe="Passend zum Limit des Mailanbieters. Rundmails werden automatisch auf mehrere Stunden verteilt; Einzelmails (z. B. Nummer zugeteilt) haben Vorrang." />
                <x-ui.feld name="erinnerung_aufgaben_tage" label="Aufgaben-Erinnerung (Tage vor Fälligkeit)" typ="number" :wert="$werte['erinnerung_aufgaben_tage']" required />
            </div>
            <p class="mt-4 text-sm text-stone-600">Versand über: <strong>{{ $mailer }}</strong> · Postfach (IMAP): <strong>{{ $imap ?? 'nicht eingerichtet' }}</strong></p>
            <p class="text-xs text-stone-500">Zugangsdaten für Versand und Postfach stehen aus Sicherheitsgründen nur in der Server-Konfiguration (.env).</p>
        </x-ui.karte>

        <x-ui.knopf>Speichern</x-ui.knopf>
    </form>
</x-layouts.admin>
