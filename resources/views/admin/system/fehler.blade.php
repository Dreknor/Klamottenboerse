<x-layouts.admin titel="Fehlerprotokoll">
    <x-ui.kopf titel="Fehlerprotokoll" unter="Warnungen und Fehler der Anwendung. Gleiche Fehler werden zusammengefasst und gezählt.">
        <form method="post" action="{{ route('admin.fehler.leeren') }}" onsubmit="return confirm('Alle erledigten Einträge löschen?')">
            @csrf @method('delete')
            <x-ui.knopf art="sekundaer">Erledigte löschen</x-ui.knopf>
        </form>
    </x-ui.kopf>

    <x-ui.karte>
        <div class="mb-4 flex flex-wrap gap-1 text-sm">
            @foreach (['offen' => 'Offen', 'erledigt' => 'Erledigt', 'alle' => 'Alle'] as $wert => $text)
                <a href="{{ route('admin.fehler.index', ['ansicht' => $wert]) }}"
                   class="rounded-full border px-3 py-1 no-underline {{ $ansicht === $wert ? 'border-marke-600 bg-marke-50 text-marke-800' : 'border-stone-300 text-stone-600 hover:bg-stone-100' }}">{{ $text }}</a>
            @endforeach
        </div>
        <div class="overflow-x-auto">
            <table class="tabelle">
                <thead><tr><th>Zuletzt</th><th>Art</th><th>Meldung</th><th>Wo</th><th class="text-right">Anzahl</th><th></th></tr></thead>
                <tbody>
                @forelse ($fehler as $f)
                    <tr class="{{ $f->erledigt_at ? 'text-stone-400' : '' }}">
                        <td class="whitespace-nowrap text-sm">{{ $f->zuletzt_at?->isoFormat('D.M. HH:mm') }}</td>
                        <td><x-ui.abzeichen :farbe="$f->farbe()">{{ $f->stufe }}</x-ui.abzeichen></td>
                        <td class="max-w-md"><a href="{{ route('admin.fehler.show', $f) }}" class="line-clamp-2">{{ $f->nachricht }}</a></td>
                        <td class="max-w-xs truncate text-xs text-stone-500" title="{{ $f->url }}">{{ $f->methode }} {{ \Illuminate\Support\Str::after($f->url ?? '', config('app.url')) }}</td>
                        <td class="text-right">{{ $f->anzahl }}</td>
                        <td>
                            <form method="post" action="{{ route('admin.fehler.erledigt', $f) }}">@csrf
                                <button class="whitespace-nowrap text-sm text-marke-700 hover:underline">{{ $f->erledigt_at ? 'wieder öffnen' : 'erledigt' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-stone-500">Keine Einträge. 🎉</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $fehler->links() }}</div>
    </x-ui.karte>
</x-layouts.admin>
