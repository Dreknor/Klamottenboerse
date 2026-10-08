<x-layouts.admin :titel="$mail->betreff ?: 'Mail'">
    <x-ui.kopf :titel="$mail->betreff ?: '(ohne Betreff)'" :unter="'Von '.($mail->von_name ? $mail->von_name.' <'.$mail->von_email.'>' : $mail->von_email).' · '.$mail->empfangen_at->format('d.m.Y H:i')">
        <form method="post" action="{{ route('admin.posteingang.status', $mail) }}" class="flex gap-2">
            @csrf
            @if ($mail->erledigt_at || $mail->spam)
                <x-ui.knopf art="sekundaer" name="aktion" value="offen">Wieder öffnen</x-ui.knopf>
            @else
                <x-ui.knopf art="sekundaer" name="aktion" value="erledigt">Erledigt</x-ui.knopf>
                <x-ui.knopf art="leise" name="aktion" value="spam">Spam</x-ui.knopf>
            @endif
        </form>
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.karte>
                @if (filled($mail->html))
                    @if (! $bilderLaden && \App\Domain\Kommunikation\Mailinhalt::hatExterneBilder($mail->html))
                        <p class="mb-3 flex flex-wrap items-center gap-2 rounded-lg bg-stone-100 px-3 py-2 text-sm text-stone-600">
                            Bilder aus dem Internet sind zum Schutz der Privatsphäre ausgeblendet.
                            <a href="{{ request()->fullUrlWithQuery(['bilder' => 1]) }}">Bilder anzeigen</a>
                        </p>
                    @endif
                    {{-- Abgeschottet: keine Skripte, keine Formulare; Links öffnen in neuem Tab --}}
                    <iframe title="Inhalt der Mail" class="block min-h-48 w-full rounded border-0"
                            sandbox="allow-same-origin allow-popups allow-popups-to-escape-sandbox"
                            srcdoc="{{ \App\Domain\Kommunikation\Mailinhalt::htmlDokument($mail->html, $bilderLaden) }}"
                            onload="this.style.height = (this.contentDocument.documentElement.scrollHeight + 8) + 'px'"></iframe>
                    <details class="mt-3 text-sm text-stone-500">
                        <summary class="cursor-pointer">Als reinen Text anzeigen</summary>
                        <div class="mail-text mt-2 text-stone-800">{{ \App\Domain\Kommunikation\Mailinhalt::textAlsHtml((string) $mail->text) }}</div>
                    </details>
                @else
                    <div class="mail-text">{{ \App\Domain\Kommunikation\Mailinhalt::textAlsHtml((string) $mail->text) }}</div>
                @endif
                @if ($mail->anhaenge)
                    <p class="mt-4 border-t border-stone-100 pt-3 text-sm text-stone-500">
                        Anhänge (im Postfach abrufbar): {{ collect($mail->anhaenge)->pluck('name')->implode(', ') }}
                    </p>
                @endif
            </x-ui.karte>

            <x-ui.karte titel="Antworten">
                <form method="get" class="mb-3 flex gap-2">
                    <select name="vorlage" class="feld" onchange="this.form.submit()">
                        <option value="">Textbaustein aus Mailvorlage übernehmen …</option>
                        @foreach ($vorlagen as $id => $name)<option value="{{ $id }}" @selected(request('vorlage') == $id)>{{ $name }}</option>@endforeach
                    </select>
                </form>
                <form method="post" action="{{ route('admin.posteingang.antworten', $mail) }}" class="space-y-3">
                    @csrf
                    <textarea name="text" rows="10" class="feld" required placeholder="Hallo …">{{ old('text', $antwortText ?: 'Hallo '.($mail->person?->vorname ?? $vorname).",\n\n\n\nViele Grüße\n".auth()->user()->vorname.' vom Klamottenbörsen-Team') }}</textarea>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-ui.knopf>Antwort senden</x-ui.knopf>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="erledigt" value="1" checked> danach als erledigt markieren</label>
                    </div>
                </form>
                @foreach ($antworten as $a)
                    <p class="mt-3 text-xs text-stone-500">Frühere Antwort: {{ $a->betreff }} ({{ ($a->versendet_at ?? $a->created_at)->format('d.m.Y') }})</p>
                @endforeach
            </x-ui.karte>
        </div>

        <x-ui.karte titel="Absender">
            @if ($mail->person)
                <p class="font-medium"><a href="{{ route('admin.personen.show', $mail->person) }}">{{ $mail->person->name }}</a></p>
                <p class="text-sm text-stone-500">{{ $mail->person->email }} {{ $mail->person->telefon }}</p>
                @if ($boerse)
                    <div class="mt-4 border-t border-stone-100 pt-3">
                        <p class="text-sm text-stone-500">{{ $boerse->titel }}</p>
                        @if ($teilnahme)
                            <p class="mt-1">Nummer <strong>{{ $teilnahme->nummer ?? '–' }}</strong> · <x-ui.abzeichen :farbe="$teilnahme->status->farbe()">{{ $teilnahme->status->label() }}</x-ui.abzeichen></p>
                        @else
                            <p class="mt-1 text-sm">Noch nicht angemeldet.</p>
                            <form method="post" action="{{ route('admin.teilnahmen.store') }}" class="mt-2">
                                @csrf
                                <input type="hidden" name="person_id" value="{{ $mail->person->id }}">
                                <x-ui.knopf groesse="klein">Als Verkäufer anmelden</x-ui.knopf>
                            </form>
                        @endif
                    </div>
                @endif
            @else
                <p class="mb-3 text-sm text-stone-600">Diese Adresse ist noch keiner Person zugeordnet.</p>
                @foreach ($aehnliche as $p)
                    <form method="post" action="{{ route('admin.posteingang.zuordnen', $mail) }}" class="mb-2 flex items-center justify-between gap-2 text-sm">
                        @csrf<input type="hidden" name="person_id" value="{{ $p->id }}">
                        <span>{{ $p->name }} <span class="text-stone-500">{{ $p->email }}</span></span>
                        <x-ui.knopf art="sekundaer" groesse="klein">Zuordnen</x-ui.knopf>
                    </form>
                @endforeach
                <form method="post" action="{{ route('admin.posteingang.zuordnen', $mail) }}" class="mt-3 space-y-2 border-t border-stone-100 pt-3">
                    @csrf
                    <p class="text-sm font-medium">Neue Person anlegen</p>
                    <input name="vorname" value="{{ $vorname }}" class="feld" placeholder="Vorname" required>
                    <input name="nachname" value="{{ $nachname }}" class="feld" placeholder="Nachname" required>
                    <x-ui.knopf groesse="klein">Anlegen und zuordnen</x-ui.knopf>
                </form>
            @endif
        </x-ui.karte>
    </div>
</x-layouts.admin>
