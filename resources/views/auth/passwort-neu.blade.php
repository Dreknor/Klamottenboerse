<x-layouts.oeffentlich titel="Neues Passwort">
    <div class="mx-auto max-w-sm">
        <x-ui.karte titel="Neues Passwort festlegen">
            <form method="post" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-ui.feld name="email" label="E-Mail" typ="email" :wert="$email" required autocomplete="username" />
                <x-ui.feld name="password" label="Neues Passwort (mind. 10 Zeichen)" typ="password" required autocomplete="new-password" />
                <x-ui.feld name="password_confirmation" label="Passwort wiederholen" typ="password" required autocomplete="new-password" />
                <x-ui.knopf class="w-full">Passwort speichern</x-ui.knopf>
            </form>
        </x-ui.karte>
    </div>
</x-layouts.oeffentlich>
