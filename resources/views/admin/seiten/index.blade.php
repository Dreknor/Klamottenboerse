<x-layouts.admin titel="Website">
    <x-ui.kopf titel="Website-Seiten" unter="Startseite und Infoseiten bestehen aus Bausteinen. Änderungen sind erst nach „Veröffentlichen“ sichtbar." />

    @if ($unvollstaendig)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
            Die Angaben zum Betreiber (Name, Anschrift, E-Mail) fehlen noch. Ein Admin trägt sie unter
            <a href="{{ route('admin.einstellungen.edit') }}">Einstellungen</a> ein – sie erscheinen dann automatisch im Impressum und in der Datenschutzerklärung.
        </div>
    @endif

    <x-ui.karte>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Seite</th><th>Adresse</th><th>Stand</th><th>Im Menü</th><th></th></tr></thead>
                @foreach ($seiten as $seite)
                <tbody x-data="{ einstellungen: false }">
                    @php $adresse = $seite->slug === 'start' ? '/' : '/'.$seite->slug; @endphp
                    <tr>
                        <td><a href="{{ route('admin.seiten.edit', $seite) }}" class="font-medium">{{ $seite->titel }}</a></td>
                        <td>@if ($seite->veroeffentlicht_at)<a href="{{ url($adresse) }}" target="_blank">{{ $adresse }}</a>@else<span class="text-stone-500">{{ $adresse }}</span>@endif</td>
                        <td>
                            @if (! $seite->veroeffentlicht_at)
                                <x-ui.abzeichen farbe="amber">noch nicht veröffentlicht</x-ui.abzeichen>
                            @elseif ($seite->hatEntwurf())
                                <x-ui.abzeichen farbe="sky">Entwurf offen</x-ui.abzeichen>
                            @else
                                <span class="text-sm text-stone-500">{{ $seite->updated_at->format('d.m.Y') }} {{ $seite->bearbeitetVon?->name }}</span>
                            @endif
                        </td>
                        <td>{{ $seite->im_menue ? 'Platz '.$seite->menue_reihenfolge : '–' }}</td>
                        <td class="whitespace-nowrap text-right">
                            @if ($seite->slug !== 'start' && $seite->istBausteinSeite())
                                <button type="button" class="text-sm text-marke-700 hover:underline" @click="einstellungen = !einstellungen">Menü & Beschreibung</button>
                            @endif
                        </td>
                    </tr>
                    @if ($seite->slug !== 'start' && $seite->istBausteinSeite())
                        <tr x-show="einstellungen" x-cloak>
                            <td colspan="5">
                                <form method="post" action="{{ route('admin.seiten.einstellungen', $seite) }}" class="grid gap-3 rounded-lg bg-stone-50 p-3 md:grid-cols-4 md:items-end">
                                    @csrf @method('put')
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="hidden" name="im_menue" value="0">
                                        <input type="checkbox" name="im_menue" value="1" @checked($seite->im_menue) @disabled(! $seite->veroeffentlicht_at)> Im Menü zeigen
                                    </label>
                                    <x-ui.feld name="menue_reihenfolge" label="Position im Menü" typ="number" :wert="$seite->menue_reihenfolge" />
                                    <x-ui.feld name="beschreibung" label="Kurzbeschreibung (für Suchmaschinen)" :wert="$seite->beschreibung" />
                                    <div class="flex gap-3">
                                        <x-ui.knopf groesse="klein">Speichern</x-ui.knopf>
                                    </div>
                                </form>
                                @unless (in_array($seite->slug, \App\Http\Controllers\Admin\SeiteController::SYSTEM, true))
                                    <form method="post" action="{{ route('admin.seiten.destroy', $seite) }}" class="mt-2" onsubmit="return confirm('Seite „{{ $seite->titel }}“ löschen?')">
                                        @csrf @method('delete')<button class="text-sm text-red-700 hover:underline">Seite löschen</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @endif
                </tbody>
                @endforeach
            </table>
        </div>
    </x-ui.karte>

    <x-ui.karte titel="Neue Seite" class="mt-6">
        <form method="post" action="{{ route('admin.seiten.store') }}" class="flex flex-wrap items-end gap-3">
            @csrf
            <x-ui.feld name="titel" label="Titel" placeholder="z. B. Spenden und Förderverein" required />
            <x-ui.knopf>Anlegen</x-ui.knopf>
        </form>
        <p class="mt-2 text-sm text-stone-500">Die Adresse ergibt sich aus dem Titel, z. B. /spenden-und-foerderverein.</p>
    </x-ui.karte>
</x-layouts.admin>
