<x-layouts.oeffentlich titel="Anmelden">
    <div class="mx-auto max-w-sm">
        <x-ui.karte titel="Anmelden für das Team">
            <form method="post" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <x-ui.feld name="email" label="E-Mail" typ="email" autocomplete="username" required autofocus />
                <x-ui.feld name="password" label="Passwort" typ="password" autocomplete="current-password" required />
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="merken" value="1"> Angemeldet bleiben</label>
                <x-ui.knopf class="w-full">Anmelden</x-ui.knopf>
                <p class="text-center text-sm"><a href="{{ route('password.request') }}">Passwort vergessen?</a></p>
            </form>
        </x-ui.karte>
        <p class="mt-4 text-center text-sm text-stone-600">
            Verkäufer und Helfer brauchen kein Passwort:
            <a href="{{ route('portal.link') }}">Link zum Portal per Mail anfordern</a>
        </p>
    </div>
</x-layouts.oeffentlich>
