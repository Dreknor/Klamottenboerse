<x-layouts.admin titel="Neue Mailvorlage">
    <x-ui.kopf titel="Neue Mailvorlage" unter="Eigene Vorlagen kannst du im Mailplan einplanen oder für Antworten im Posteingang nutzen.">
        <x-ui.knopf art="sekundaer" :href="route('admin.mailvorlagen.index')">Alle Vorlagen</x-ui.knopf>
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.karte titel="Text">
            <form method="post" action="{{ route('admin.mailvorlagen.store') }}" class="space-y-4">
                @csrf
                <x-ui.feld name="name" label="Name (intern)" required />
                <x-ui.feld name="betreff" label="Betreff" required />
                <x-ui.feld name="inhalt" label="Text" typ="textarea" rows="16" required
                           hilfe="**fett**, Aufzählungen mit „- “ und Links als [Text](Adresse) sind möglich." />
                <label class="flex items-start gap-2 text-sm">
                    <input type="hidden" name="push" value="0">
                    <input type="checkbox" name="push" value="1" class="mt-0.5" @checked(old('push'))>
                    <span>Zusätzlich als Push-Nachricht – an alle, die Push auf ihrem Handy eingeschaltet haben (gut für Erinnerungen).</span>
                </label>
                <x-ui.knopf>Anlegen</x-ui.knopf>
            </form>
        </x-ui.karte>

        <x-ui.karte titel="Platzhalter">
            <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                @foreach ($platzhalter as $name => $text)
                    <dt class="font-mono text-marke-700">{{ '{'.$name.'}' }}</dt><dd class="text-stone-600">{{ $text }}</dd>
                @endforeach
            </dl>
            <p class="mt-3 text-sm text-stone-500">Nach dem Anlegen siehst du eine Vorschau mit deinen Daten.</p>
        </x-ui.karte>
    </div>
</x-layouts.admin>
