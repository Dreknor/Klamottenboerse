@php $neu = ! $person->exists; @endphp
<x-layouts.admin :titel="$neu ? 'Neue Person' : $person->name">
    <x-ui.kopf :titel="$neu ? 'Neue Person' : $person->name.' bearbeiten'" />

    <form method="post" action="{{ $neu ? route('admin.personen.store') : route('admin.personen.update', $person) }}" class="space-y-6">
        @csrf
        @unless ($neu) @method('put') @endunless
        <x-ui.karte titel="Kontakt">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.feld name="vorname" label="Vorname" :wert="$person->vorname" required />
                <x-ui.feld name="nachname" label="Nachname" :wert="$person->nachname" required />
                <x-ui.feld name="email" label="E-Mail" typ="email" :wert="$person->email" hilfe="Optional – z. B. bei Helfern, die nur telefonisch erreichbar sind." />
                <x-ui.feld name="telefon" label="Telefon" :wert="$person->telefon" />
                <x-ui.auswahl name="kinderhaus_bezug" label="Bezug zum Kinderhaus" :wert="$person->kinderhaus_bezug?->value"
                              :optionen="collect(\App\Enums\KinderhausBezug::cases())->mapWithKeys(fn ($b) => [$b->value => $b->label()])" />
                <label class="flex items-center gap-2 self-end pb-2">
                    <input type="hidden" name="info_mails" value="0">
                    <input type="checkbox" name="info_mails" value="1" @checked(old('info_mails', $person->info_mails_erlaubt_at !== null))>
                    Möchte über künftige Börsen informiert werden
                </label>
            </div>
        </x-ui.karte>

        @role('admin')
            <x-ui.karte titel="Zugang zum Team-Bereich">
                <p class="mb-3 text-sm text-stone-600">Verkäufer und Helfer brauchen keine Rolle – sie kommen per Link ins Portal.</p>
                <div class="space-y-2">
                    @foreach (\Database\Seeders\GrunddatenSeeder::ROLLEN as $rolle => $text)
                        <label class="flex items-center gap-2"><input type="checkbox" name="rollen[]" value="{{ $rolle }}" @checked($person->exists && $person->hasRole($rolle))> {{ $text }}</label>
                    @endforeach
                </div>
                <x-ui.feld name="password" label="Neues Passwort (mind. 10 Zeichen)" typ="password" autocomplete="new-password" class="mt-4 max-w-sm" hilfe="Nur nötig für Orga, Kasse und Annahme. Leer lassen = unverändert." />
            </x-ui.karte>
        @endrole

        <x-ui.knopf>Speichern</x-ui.knopf>
    </form>
</x-layouts.admin>
