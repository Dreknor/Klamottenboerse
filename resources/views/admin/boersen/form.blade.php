@php
    $neu = ! $boerse->exists;
    $dt = fn ($wert) => $wert?->format('Y-m-d\TH:i');
@endphp
<x-layouts.admin :titel="$neu ? 'Neue Börse' : 'Börse bearbeiten'">
    <x-ui.kopf :titel="$neu ? 'Neue Börse anlegen' : $boerse->titel" />

    @if ($neu && $vorlagen->isNotEmpty())
        <x-ui.karte titel="Schnell: Kopie einer bisherigen Börse" class="mb-6">
            <p class="mb-4 text-sm text-stone-600">Übernimmt alle Einstellungen, Uhrzeiten, Schichten, den Mailplan und die Checkliste. Alle Termine werden passend zum neuen Verkaufstag verschoben.</p>
            <form method="post" action="{{ route('admin.boersen.store') }}" class="grid gap-4 md:grid-cols-4 md:items-end">
                @csrf
                <x-ui.auswahl name="vorlage_id" label="Vorlage" :optionen="$vorlagen" class="md:col-span-2" />
                <x-ui.feld name="verkaufstag" label="Neuer Verkaufstag" typ="date" required />
                <x-ui.feld name="titel" label="Titel" wert="Klamottenbörse" required />
                <div class="md:col-span-4"><x-ui.knopf>Als Kopie anlegen</x-ui.knopf></div>
            </form>
        </x-ui.karte>
        <h2 class="mb-3">… oder ganz neu</h2>
    @endif

    <form method="post" action="{{ $neu ? route('admin.boersen.store') : route('admin.boersen.update', $boerse) }}" class="space-y-6">
        @csrf
        @unless ($neu) @method('put') @endunless

        <x-ui.karte titel="Grunddaten">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.feld name="titel" label="Titel" :wert="$boerse->titel" required />
                <x-ui.feld name="verkaufstag" label="Verkaufstag" typ="date" :wert="$boerse->verkaufstag?->format('Y-m-d')" required />
                <x-ui.feld name="ort_name" label="Ort" :wert="$boerse->ort?->name" list="orte" hilfe="z. B. Luthersaal der Friedenskirche" />
                <x-ui.feld name="ort_adresse" label="Adresse" :wert="$boerse->ort?->adresse" />
            </div>
            <datalist id="orte">@foreach ($orte as $ort)<option value="{{ $ort->name }}">@endforeach</datalist>
        </x-ui.karte>

        <x-ui.karte titel="Termine">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.feld name="anmeldung_kinderhaus_ab" label="Anmeldestart Kinderhaus-Familien" typ="datetime-local" :wert="$dt($boerse->anmeldung_kinderhaus_ab)" />
                <x-ui.feld name="anmeldung_ab" label="Anmeldestart für alle" typ="datetime-local" :wert="$dt($boerse->anmeldung_ab)" />
                <x-ui.feld name="anlieferung_beginn" label="Annahme der Kisten ab" typ="datetime-local" :wert="$dt($boerse->anlieferung_beginn)" />
                <x-ui.feld name="anlieferung_ende" label="Annahme bis" typ="datetime-local" :wert="$dt($boerse->anlieferung_ende)" />
                <x-ui.feld name="verkauf_beginn" label="Verkauf ab" typ="datetime-local" :wert="$dt($boerse->verkauf_beginn)" />
                <x-ui.feld name="verkauf_ende" label="Verkauf bis" typ="datetime-local" :wert="$dt($boerse->verkauf_ende)" />
                <x-ui.feld name="abholung_beginn" label="Abholung ab" typ="datetime-local" :wert="$dt($boerse->abholung_beginn)" />
                <x-ui.feld name="abholung_ende" label="Abholung bis" typ="datetime-local" :wert="$dt($boerse->abholung_ende)" />
            </div>
        </x-ui.karte>

        <x-ui.karte titel="Nummern und Kapazität">
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.feld name="nummer_von" label="Nummern von" typ="number" :wert="$boerse->nummer_von" required />
                <x-ui.feld name="nummer_bis" label="Nummern bis" typ="number" :wert="$boerse->nummer_bis" required />
                <x-ui.feld name="kapazitaet" label="Plätze (Verkäufer)" typ="number" :wert="$boerse->kapazitaet" required />
                <x-ui.feld name="block_toleranz" label="Erlaubte Abweichung je Block" typ="number" :wert="$boerse->block_toleranz" required />
                <x-ui.feld name="kinderhaus_nummer" label="Feste Nummer Kinderhaus" typ="number" :wert="$boerse->kinderhaus_nummer" required hilfe="Wird automatisch erfasst, ohne Spende" />
                <x-ui.feld name="angebot_stunden" label="Warteliste: Angebot gilt (Stunden)" typ="number" :wert="$boerse->angebot_stunden" required />
                <x-ui.feld name="max_teile" label="Max. Teile je Verkäufer" typ="number" :wert="$boerse->max_teile" />
                <x-ui.feld name="max_kisten" label="Max. Kisten je Verkäufer" typ="number" :wert="$boerse->max_kisten" />
            </div>
        </x-ui.karte>

        <x-ui.karte titel="Abrechnung">
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.feld name="provision_prozent" label="Spende in %" typ="number" step="0.1" :wert="$boerse->provision_promille / 10" required />
                <x-ui.auswahl name="rundung_cent" label="Auszahlung abrunden auf" :wert="$boerse->rundung_cent"
                              :optionen="[1 => '1 Cent (keine Rundung)', 5 => '5 Cent', 10 => '10 Cent', 50 => '50 Cent', 100 => '1 Euro']" />
                <label class="flex items-center gap-2 self-end pb-2">
                    <input type="hidden" name="live_erloes_freigegeben" value="0">
                    <input type="checkbox" name="live_erloes_freigegeben" value="1" @checked(old('live_erloes_freigegeben', $boerse->live_erloes_freigegeben))>
                    Verkäufer sehen ihren Erlös live im Portal
                </label>
            </div>
            <x-ui.feld name="hinweise" label="Interne Hinweise" typ="textarea" :wert="$boerse->hinweise" class="mt-4" />
        </x-ui.karte>

        <x-ui.knopf>{{ $neu ? 'Börse anlegen' : 'Speichern' }}</x-ui.knopf>
    </form>
</x-layouts.admin>
