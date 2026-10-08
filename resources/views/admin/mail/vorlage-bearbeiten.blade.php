<x-layouts.admin :titel="$vorlage->name">
    <x-ui.kopf :titel="'Vorlage: '.$vorlage->name">
        <x-ui.knopf art="sekundaer" :href="route('admin.mailvorlagen.index')">Alle Vorlagen</x-ui.knopf>
        @unless ($vorlage->istStandard())
            <form method="post" action="{{ route('admin.mailvorlagen.destroy', $vorlage) }}"
                  onsubmit="return confirm('Vorlage löschen? Einträge im Mailplan, die sie verwenden, werden ebenfalls entfernt.')">
                @csrf @method('delete')
                <x-ui.knopf art="gefahr">Löschen</x-ui.knopf>
            </form>
        @endunless
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.karte titel="Text bearbeiten">
            <form method="post" action="{{ route('admin.mailvorlagen.update', $vorlage) }}" class="space-y-4">
                @csrf @method('put')
                <x-ui.feld name="name" label="Name (intern)" :wert="$vorlage->name" required />
                <x-ui.feld name="betreff" label="Betreff" :wert="$vorlage->betreff" required />
                <x-ui.feld name="inhalt" label="Text" typ="textarea" :wert="$vorlage->inhalt" rows="16" required
                           hilfe="**fett**, Aufzählungen mit „- “ und Links als [Text](Adresse) sind möglich." />
                <label class="flex items-start gap-2 text-sm">
                    <input type="hidden" name="push" value="0">
                    <input type="checkbox" name="push" value="1" class="mt-0.5" @checked($vorlage->push)>
                    <span>Zusätzlich als Push-Nachricht – an alle, die Push auf ihrem Handy eingeschaltet haben (gut für Erinnerungen).</span>
                </label>
                <x-ui.knopf>Speichern</x-ui.knopf>
            </form>
        </x-ui.karte>

        <div class="space-y-6">
            <x-ui.karte titel="Vorschau (mit deinen Daten und der aktuellen Börse)">
                <p class="mb-3 border-b border-stone-200 pb-2 font-medium">{{ $vorschauBetreff }}</p>
                <div class="prose-sm space-y-2 [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5">{!! $vorschauHtml !!}</div>
            </x-ui.karte>
            <x-ui.karte titel="Platzhalter">
                <dl class="grid grid-cols-[auto_1fr] gap-x-3 gap-y-1 text-sm">
                    @foreach ($platzhalter as $name => $text)
                        <dt class="font-mono text-marke-700">{{ '{'.$name.'}' }}</dt><dd class="text-stone-600">{{ $text }}</dd>
                    @endforeach
                    <dt class="font-mono text-marke-700">{absage_link}</dt><dd class="text-stone-600">Absage-Link (Verkäufer-Mails)</dd>
                    <dt class="font-mono text-marke-700">{angebot_link}, {angebot_bis}</dt><dd class="text-stone-600">nur Warteliste-Angebot</dd>
                    <dt class="font-mono text-marke-700">{feedback_link}</dt><dd class="text-stone-600">nur Feedback-Mail</dd>
                </dl>
            </x-ui.karte>
        </div>
    </div>
</x-layouts.admin>
