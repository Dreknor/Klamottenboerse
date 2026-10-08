<x-layouts.oeffentlich titel="Als Verkäufer anmelden">
    <div class="mx-auto max-w-xl">
        <h1>Als Verkäufer anmelden</h1>

        @if (! $boerse)
            <p class="mt-4">Der nächste Termin steht noch nicht fest. Schau bald wieder vorbei!</p>
        @elseif (! $offenFuerKinderhaus)
            <p class="mt-4 rounded-xl bg-amber-50 p-4 text-amber-900">
                Die Anmeldung für die Börse am {{ $boerse->verkaufstag->isoFormat('D. MMMM YYYY') }} startet am
                <strong>{{ ($boerse->anmeldung_kinderhaus_ab ?? $boerse->anmeldung_ab)?->isoFormat('D. MMMM [um] H:mm [Uhr]') }}</strong>.
            </p>
        @else
            <p class="mt-2 text-stone-600">
                Klamottenbörse am <strong>{{ $boerse->verkaufstag->isoFormat('dddd, D. MMMM YYYY') }}</strong>.
                Die Nummern werden in der Reihenfolge vergeben, in der die Anmeldungen per Mail bestätigt werden.
            </p>
            @unless ($offenFuerAlle)
                <p class="mt-3 rounded-xl bg-sky-50 p-4 text-sky-900">Gerade können sich nur Familien und Mitarbeitende des Kinderhauses anmelden. Für alle startet die Anmeldung am {{ $boerse->anmeldung_ab?->isoFormat('D. MMMM [um] H:mm [Uhr]') }}.</p>
            @endunless

            <form method="post" action="{{ route('anmeldung.store') }}" class="mt-6 space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-stone-200">
                @csrf
                <div class="hidden" aria-hidden="true"><label>Webseite <input name="webseite" tabindex="-1" autocomplete="off"></label></div>
                <div class="grid gap-4 md:grid-cols-2">
                    <x-ui.feld name="vorname" label="Vorname" required autocomplete="given-name" />
                    <x-ui.feld name="nachname" label="Nachname" required autocomplete="family-name" />
                </div>
                <x-ui.feld name="email" label="E-Mail" typ="email" required autocomplete="email" hilfe="Hierhin schicken wir den Bestätigungslink und deine Nummer." />
                <x-ui.feld name="telefon" label="Telefon (optional)" typ="tel" autocomplete="tel" />
                <x-ui.auswahl name="kinderhaus_bezug" label="Bezug zum Kinderhaus" :optionen="[
                    'keiner' => 'Nein',
                    'familie' => 'Ja, unser Kind geht ins Kinderhaus',
                    'mitarbeiter' => 'Ja, ich arbeite im Kinderhaus',
                ]" />
                <x-kategorien-auswahl class="rounded-lg border border-stone-200 p-4" />
                <label class="flex items-start gap-2"><input type="checkbox" name="info_mails" value="1" class="mt-1" @checked(old('info_mails', true))> <span>Ich möchte per Mail erfahren, wenn die Anmeldung für künftige Börsen startet. (Jederzeit abbestellbar)</span></label>
                <label class="flex items-start gap-2"><input type="checkbox" name="datenschutz" value="1" class="mt-1" required> <span>Ich bin einverstanden, dass meine Angaben für die Organisation der Klamottenbörse gespeichert werden.</span></label>
                <x-ui.knopf groesse="gross" class="w-full">Anmeldung absenden</x-ui.knopf>
            </form>
        @endif
    </div>
</x-layouts.oeffentlich>
