<x-layouts.admin titel="Team & Rechte">
    <x-ui.kopf titel="Team & Rechte" :unter="$team->count().' Personen mit Zugang. Wer hier keine Rolle hat, kommt nur ins Verkäufer-Portal.'" />

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            @foreach ($team as $person)
                <x-ui.karte>
                    <form method="post" action="{{ route('admin.team.update', $person) }}">
                        @csrf @method('put')
                        <div class="flex flex-wrap items-start justify-between gap-2">
                            <div>
                                <a href="{{ route('admin.personen.show', $person) }}" class="font-semibold">{{ $person->name }}</a>
                                @if ($person->is(auth()->user()))<x-ui.abzeichen>du</x-ui.abzeichen>@endif
                                <p class="text-sm text-stone-500">{{ $person->email ?? 'keine E-Mail' }}{{ $person->telefon ? ' · '.$person->telefon : '' }}</p>
                                @unless ($person->password)
                                    <p class="text-sm text-amber-800">Hat noch kein Passwort – Link schicken, damit sie sich anmelden kann.</p>
                                @endunless
                            </div>
                            <x-ui.knopf groesse="klein">Speichern</x-ui.knopf>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($rollen as $rolle => $text)
                                <label class="flex items-start gap-2 text-sm">
                                    <input type="checkbox" name="rollen[]" value="{{ $rolle }}" class="mt-0.5" @checked($person->hasRole($rolle))>
                                    <span>{{ $text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </form>
                    <div class="mt-3 flex flex-wrap gap-4 border-t border-stone-100 pt-3 text-sm">
                        @if ($person->email)
                            <form method="post" action="{{ route('admin.team.passwort-link', $person) }}">@csrf
                                <button class="text-marke-700 hover:underline">{{ $person->password ? 'Link zum Passwort-Zurücksetzen schicken' : 'Einladung schicken' }}</button>
                            </form>
                        @endif
                        @unless ($person->is(auth()->user()))
                            <form method="post" action="{{ route('admin.team.destroy', $person) }}" onsubmit="return confirm('{{ $person->name }} aus dem Team entfernen? Die Person bleibt in der Kartei, kann sich aber nicht mehr im Backend anmelden.')">
                                @csrf @method('delete')
                                <button class="text-red-700 hover:underline">Aus dem Team entfernen</button>
                            </form>
                        @endunless
                    </div>
                </x-ui.karte>
            @endforeach
        </div>

        <x-ui.karte titel="Ins Team aufnehmen" x-data="{ neu: {{ old('nachname') ? 'true' : 'false' }} }">
            <form method="post" action="{{ route('admin.team.store') }}" class="space-y-4">
                @csrf
                <div x-show="!neu">
                    <x-ui.personen-auswahl label="Bekannte Person" />
                    <button type="button" class="mt-2 text-sm text-marke-700 hover:underline" @click="neu = true">Person ist noch nicht in der Kartei</button>
                </div>
                <template x-if="neu">
                    <div class="space-y-3">
                        <x-ui.feld name="vorname" label="Vorname" required />
                        <x-ui.feld name="nachname" label="Nachname" required />
                        <x-ui.feld name="email" label="E-Mail" typ="email" required />
                        <button type="button" class="text-sm text-marke-700 hover:underline" @click="neu = false">Doch bekannte Person wählen</button>
                    </div>
                </template>
                <fieldset>
                    <legend class="mb-1 text-sm font-medium">Darf …</legend>
                    @foreach ($rollen as $rolle => $text)
                        <label class="mt-1 flex items-start gap-2 text-sm">
                            <input type="checkbox" name="rollen[]" value="{{ $rolle }}" class="mt-0.5" @checked(in_array($rolle, old('rollen', []), true))>
                            <span>{{ $text }}</span>
                        </label>
                    @endforeach
                    @error('rollen')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
                </fieldset>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="einladen" value="1" checked> Einladung per Mail schicken (Passwort selbst festlegen)</label>
                <x-ui.knopf>Aufnehmen</x-ui.knopf>
            </form>
        </x-ui.karte>
    </div>
</x-layouts.admin>
