<x-layouts.admin titel="Website-Seiten">
    <x-ui.kopf titel="Impressum & Datenschutz" unter="Pflichtseiten der Website. Änderungen sind nach dem Speichern sofort sichtbar." />

    @if ($unvollstaendig)
        <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900">
            Die Angaben zum Betreiber (Name, Anschrift, E-Mail) fehlen noch. Ein Admin trägt sie unter
            <a href="{{ route('admin.einstellungen.edit') }}">Einstellungen</a> ein – sie erscheinen dann automatisch im Impressum und in der Datenschutzerklärung.
        </div>
    @endif

    <x-ui.karte>
        <table class="tabelle">
            <thead><tr><th>Seite</th><th>Adresse</th><th>Zuletzt geändert</th></tr></thead>
            <tbody>
            @foreach ($seiten as $seite)
                <tr>
                    <td><a href="{{ route('admin.seiten.edit', $seite) }}">{{ $seite->titel }}</a></td>
                    <td><a href="{{ url($seite->slug) }}" target="_blank">/{{ $seite->slug }}</a></td>
                    <td class="text-stone-500">{{ $seite->updated_at->format('d.m.Y') }} {{ $seite->bearbeitetVon?->name }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-ui.karte>
</x-layouts.admin>
