<x-layouts.oeffentlich titel="Passwort vergessen">
    <div class="mx-auto max-w-sm">
        <x-ui.karte titel="Passwort vergessen">
            <p class="mb-4 text-sm text-stone-600">Gib die E-Mail-Adresse deines Team-Zugangs ein. Wir schicken dir einen Link, mit dem du ein neues Passwort festlegst.</p>
            <form method="post" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <x-ui.feld name="email" label="E-Mail" typ="email" required autofocus />
                <x-ui.knopf class="w-full">Link zuschicken</x-ui.knopf>
            </form>
        </x-ui.karte>
        <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}">Zurück zur Anmeldung</a></p>
    </div>
</x-layouts.oeffentlich>
