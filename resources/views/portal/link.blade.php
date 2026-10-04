<x-layouts.oeffentlich titel="Link zum Portal">
    <div class="mx-auto max-w-md">
        <x-ui.karte titel="Dein persönlicher Link">
            <p class="mb-4 text-stone-600">Gib deine E-Mail-Adresse ein. Wir schicken dir einen Link, mit dem du ohne Passwort in dein Portal kommst.</p>
            <form method="post" action="{{ route('portal.link') }}" class="space-y-4">
                @csrf
                <x-ui.feld name="email" label="E-Mail" typ="email" required autofocus />
                <x-ui.knopf class="w-full">Link zuschicken</x-ui.knopf>
            </form>
        </x-ui.karte>
    </div>
</x-layouts.oeffentlich>
