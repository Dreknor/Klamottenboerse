<x-layouts.admin titel="Mein Konto">
    <x-ui.kopf titel="Mein Konto" :unter="$person->name.' · '.$person->email" />
    <x-ui.karte titel="Passwort ändern" class="max-w-lg">
        <form method="post" action="{{ route('admin.konto.passwort') }}" class="space-y-4">
            @csrf @method('put')
            <x-ui.feld name="aktuelles_passwort" label="Aktuelles Passwort" typ="password" required autocomplete="current-password" />
            <x-ui.feld name="password" label="Neues Passwort (mind. 10 Zeichen)" typ="password" required autocomplete="new-password" />
            <x-ui.feld name="password_confirmation" label="Neues Passwort wiederholen" typ="password" required autocomplete="new-password" />
            <x-ui.knopf>Passwort ändern</x-ui.knopf>
        </form>
    </x-ui.karte>

    <x-push-schalter class="mt-6 max-w-lg" text="Erinnerungen an fällige Aufgaben und Rundnachrichten ans Team zusätzlich aufs Handy oder den PC." />
</x-layouts.admin>
