<x-layouts.oeffentlich titel="Anmelden">
    @if (\App\Support\Demo::aktiv())
        <div class="mx-auto mb-8 max-w-2xl">
            <x-ui.karte titel="Demo: als … ausprobieren">
                <p class="mb-4 text-sm text-stone-600">Ein Klick genügt, ein Passwort brauchst du nicht. Du kannst jederzeit oben über „Rolle wechseln“ eine andere Sicht ausprobieren.
                    Wenn du dich selbst als Verkäufer anmeldest oder ins Team einlädst, gehen die Mails an deine echte Adresse.</p>
                <div class="grid gap-3 sm:grid-cols-2">
                    @foreach (\App\Support\Demo::ZUGAENGE as $rolle => [, $titel, $beschreibung])
                        <form method="post" action="{{ route('demo.anmelden', $rolle) }}">
                            @csrf
                            <button class="w-full rounded-xl border border-marke-200 bg-marke-50 px-4 py-3 text-left hover:bg-marke-100">
                                <span class="block font-semibold text-marke-800">{{ $titel }}</span>
                                <span class="block text-sm text-stone-600">{{ $beschreibung }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </x-ui.karte>
        </div>
    @endif
    <div class="mx-auto max-w-sm">
        <x-ui.karte titel="Anmelden für das Team">
            <form method="post" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <x-ui.feld name="email" label="E-Mail" typ="email" autocomplete="username" required :autofocus="! \App\Support\Demo::aktiv()" />
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
