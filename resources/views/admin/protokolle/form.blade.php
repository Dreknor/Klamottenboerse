@php $neu = ! $protokoll->exists; @endphp
<x-layouts.admin :titel="$neu ? 'Neues Protokoll' : $protokoll->titel">
    <x-ui.kopf :titel="$neu ? 'Neues Protokoll' : 'Protokoll bearbeiten'" />

    <form method="post" action="{{ $neu ? route('admin.protokolle.store') : route('admin.protokolle.update', $protokoll) }}" class="space-y-6">
        @csrf
        @unless ($neu) @method('put') @endunless
        <x-ui.karte>
            <div class="grid gap-4 md:grid-cols-4">
                <x-ui.feld name="titel" label="Titel" :wert="$protokoll->titel" class="md:col-span-2" required />
                <x-ui.feld name="datum" label="Datum" typ="date" :wert="$protokoll->datum?->format('Y-m-d')" required />
                <x-ui.auswahl name="boerse_id" label="Börse" :optionen="$boersen" :wert="$protokoll->boerse_id" leer="keine" />
                <x-ui.feld name="teilnehmende" label="Teilnehmende" :wert="$protokoll->teilnehmende" class="md:col-span-4" />
            </div>
            <div class="mt-4">
                <label for="inhalt" class="mb-1 block text-sm font-medium text-stone-700">Text</label>
                <textarea id="inhalt" name="inhalt" rows="20" class="feld font-mono text-sm">{{ old('inhalt', $protokoll->inhalt) }}</textarea>
                <p class="mt-1 text-xs text-stone-500">„## “ = Überschrift · „- “ = Aufzählung · „Beschluss: …“ wird als Beschluss hervorgehoben · „- [ ] …“ = offener Punkt, der sich per Klick in eine Aufgabe umwandeln lässt.</p>
            </div>
        </x-ui.karte>
        <x-ui.knopf>Speichern</x-ui.knopf>
    </form>
</x-layouts.admin>
