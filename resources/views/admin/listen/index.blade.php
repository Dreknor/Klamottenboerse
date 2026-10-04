<x-layouts.admin titel="Listen & Drucken">
    <x-ui.kopf titel="Listen & Drucken" :unter="$boerse->titel.' · '.$anzahl.' Verkäufer mit Nummer · '.$helfer.' Helfer-Zusagen'" />

    <div class="grid gap-6 md:grid-cols-2">
        <x-ui.karte titel="Belehrungen zum Unterschreiben">
            <p class="text-sm text-stone-600">Für die Kistenabgabe: zwei Blätter je A4-Seite (zum Durchschneiden), mit Name, Telefon, Nummer,
                Belehrungstext, Unterschrift und „Verkaufserlös erhalten“.</p>
            <div class="mt-4 flex flex-wrap items-end gap-3">
                <x-ui.knopf :href="route('admin.listen.belehrungen')" target="_blank">Alle {{ $anzahl }} drucken</x-ui.knopf>
                <form method="get" action="{{ route('admin.listen.belehrungen') }}" target="_blank" class="flex items-end gap-2">
                    <div>
                        <label for="belehrung-nummer" class="mb-1 block text-xs text-stone-500">Nur Nummer</label>
                        <input id="belehrung-nummer" name="nummer" inputmode="numeric" class="feld w-24" placeholder="215" required>
                    </div>
                    <x-ui.knopf art="sekundaer">Drucken</x-ui.knopf>
                </form>
            </div>
            <p class="mt-3 text-xs text-stone-500">Den Text änderst du unter <a href="{{ route('admin.boersen.edit', $boerse) }}">Börse bearbeiten → Belehrung</a>.</p>
        </x-ui.karte>

        <x-ui.karte titel="Verkäuferliste">
            <p class="text-sm text-stone-600">Alle Verkäufer je 100er-Block mit Telefon und Spalte für Bemerkungen – eine Seite je Block.</p>
            <x-ui.knopf :href="route('admin.listen.verkaeuferliste')" target="_blank" class="mt-4">Drucken</x-ui.knopf>
        </x-ui.karte>

        <x-ui.karte titel="Abstreichliste">
            <p class="text-sm text-stone-600">Nur die Nummern in Spalten je Block, mit Kästchen für „abgegeben“ und „abgeholt“.</p>
            <x-ui.knopf :href="route('admin.listen.abstreichliste')" target="_blank" class="mt-4">Drucken</x-ui.knopf>
        </x-ui.karte>

        <x-ui.karte titel="Helferliste">
            <p class="text-sm text-stone-600">Alle Schichten mit eingetragenen Helfern und Telefonnummern, fehlende Helfer sind markiert.</p>
            <x-ui.knopf :href="route('admin.listen.helferliste')" target="_blank" class="mt-4">Drucken</x-ui.knopf>
        </x-ui.karte>
    </div>
</x-layouts.admin>
