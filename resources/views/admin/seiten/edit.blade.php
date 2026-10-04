<x-layouts.admin :titel="$seite->titel">
    <x-ui.kopf :titel="$seite->titel.' bearbeiten'">
        <x-ui.knopf art="sekundaer" :href="url($seite->slug)" target="_blank">Seite ansehen</x-ui.knopf>
    </x-ui.kopf>

    @if ($unvollstaendig)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
            Es fehlen noch Betreiber-Angaben. Fehlende Werte erscheinen auf der Seite als „[bitte ergänzen …]“.
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2" x-data="{ text: @js(old('inhalt', $seite->inhalt)) }">
        <x-ui.karte titel="Text">
            <form method="post" action="{{ route('admin.seiten.update', $seite) }}" class="space-y-4">
                @csrf @method('put')
                <x-ui.feld name="titel" label="Überschrift" :wert="$seite->titel" required />
                <div>
                    <label for="inhalt" class="mb-1 block text-sm font-medium text-stone-700">Inhalt</label>
                    <textarea id="inhalt" name="inhalt" rows="24" class="feld font-mono text-sm" x-model="text" required></textarea>
                    <p class="mt-1 text-xs text-stone-500">„## “ am Zeilenanfang = Zwischenüberschrift, „- “ = Aufzählung, **fett**, Links als [Text](https://…). Leerzeile = neuer Absatz.</p>
                </div>
                <x-ui.knopf>Speichern und veröffentlichen</x-ui.knopf>
            </form>
        </x-ui.karte>

        <x-ui.karte titel="Platzhalter aus den Einstellungen">
            <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                @foreach ($platzhalter as $name => $beschreibung)
                    <dt class="font-mono text-marke-700">{{ '{'.$name.'}' }}</dt>
                    <dd class="text-stone-600">{{ $beschreibung }}: <span class="font-medium text-stone-800">{{ \App\Support\Einstellungen::get($name) ?: '– fehlt –' }}</span></dd>
                @endforeach
            </dl>
            <p class="mt-4 text-sm text-stone-600">Die Werte pflegt ein Admin unter <a href="{{ route('admin.einstellungen.edit') }}">Einstellungen</a>. So stehen Anschrift und Kontakt nur an einer Stelle.</p>
            <p class="mt-3 rounded-lg bg-stone-50 p-3 text-sm text-stone-600">Die Texte sind ein Entwurf. Bitte vor dem Livegang vom Träger (bzw. dessen Datenschutzbeauftragter) prüfen lassen.</p>
        </x-ui.karte>
    </div>
</x-layouts.admin>
