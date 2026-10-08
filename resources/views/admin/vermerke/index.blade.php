@php use App\Domain\Reputation\Reputation; @endphp
<x-layouts.admin titel="Reputation & Vermerke">
    <x-ui.kopf titel="Reputation & Vermerke"
               :unter="'Ab '.$schwellen['warnung'].' Punkten gibt es einen Hinweis, ab '.$schwellen['sperre'].' Punkten keine automatische Nummer mehr – nur noch eine Anfrage, über die ihr entscheidet. Es zählen die Vermerke der letzten '.$schwellen['monate'].' Monate.'">
        <x-ui.knopf :href="route('admin.vermerke.create')">Vermerk erfassen</x-ui.knopf>
    </x-ui.kopf>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-ui.karte titel="Auffällige Verkäufer">
                @if ($auffaellig->isEmpty())
                    <p class="text-stone-500">Niemand über der Hinweis-Schwelle.</p>
                @else
                    <table class="tabelle">
                        <thead><tr><th>Name</th><th>Punkte</th><th>Nummernvergabe</th></tr></thead>
                        <tbody>
                        @foreach ($auffaellig as $p)
                            <tr>
                                <td><a href="{{ route('admin.personen.show', $p) }}#vermerke">{{ $p->name }}</a></td>
                                <td class="tabular-nums">{{ (int) $p->reputation_punkte }}</td>
                                <td>
                                    @if (Reputation::automatischGesperrt($p))
                                        <x-ui.abzeichen farbe="red">nur händisch{{ $p->nummernvergabe === 'haendisch' ? ' (festgelegt)' : '' }}</x-ui.abzeichen>
                                    @elseif ($p->nummernvergabe === 'frei')
                                        <x-ui.abzeichen farbe="emerald">automatisch (freigegeben)</x-ui.abzeichen>
                                    @else
                                        <x-ui.abzeichen farbe="amber">automatisch, mit Hinweis</x-ui.abzeichen>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </x-ui.karte>

            <x-ui.karte titel="Zuletzt erfasst">
                <div class="overflow-x-auto">
                    <table class="tabelle">
                        <thead><tr><th>Datum</th><th>Verkäufer</th><th>Vermerk</th><th>Punkte</th><th>Erfasst</th></tr></thead>
                        <tbody>
                        @forelse ($vermerke as $v)
                            <tr class="{{ $v->istWirksam() ? '' : 'text-stone-400' }}">
                                <td class="whitespace-nowrap">{{ $v->created_at->format('d.m.Y') }}</td>
                                <td>@if ($v->person)<a href="{{ route('admin.personen.show', $v->person) }}#vermerke">{{ $v->person->name }}</a>@endif</td>
                                <td>{{ $v->art }}@if ($v->bemerkung)<span class="block text-xs text-stone-500">{{ $v->bemerkung }}</span>@endif</td>
                                <td class="tabular-nums">{{ $v->punkte }}</td>
                                <td class="text-xs text-stone-500">{{ \App\Models\Vermerk::QUELLEN[$v->quelle] ?? $v->quelle }} · {{ $v->erfasser?->vorname }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-stone-500">Noch keine Vermerke.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $vermerke->links() }}</div>
            </x-ui.karte>
        </div>

        <div class="space-y-6">
            <x-ui.karte titel="Schwellen">
                <form method="post" action="{{ route('admin.reputation.schwellen') }}" class="space-y-3">
                    @csrf @method('put')
                    <x-ui.feld name="reputation_warnung_ab" label="Hinweis ab Punkten" typ="number" :wert="$schwellen['warnung']" min="1" required />
                    <x-ui.feld name="reputation_sperre_ab" label="Nur händische Vergabe ab Punkten" typ="number" :wert="$schwellen['sperre']" min="1" required />
                    <x-ui.feld name="reputation_zeitraum_monate" label="Vermerke zählen (Monate)" typ="number" :wert="$schwellen['monate']" min="1" required />
                    <x-ui.knopf groesse="klein">Speichern</x-ui.knopf>
                </form>
            </x-ui.karte>

            <x-ui.karte titel="Arten von Vermerken">
                <div class="space-y-2">
                    @foreach ($arten as $art)
                        <form method="post" action="{{ route('admin.vermerk-arten.update', $art) }}" class="flex flex-wrap items-center gap-2 {{ $art->aktiv ? '' : 'opacity-60' }}">
                            @csrf @method('put')
                            <input name="name" value="{{ $art->name }}" class="feld min-w-0 flex-1 py-1 text-sm" required aria-label="Name">
                            <input name="punkte" value="{{ $art->punkte }}" type="number" min="0" max="20" class="feld w-16 py-1 text-sm" required aria-label="Punkte" title="Punkte">
                            <input type="hidden" name="sortierung" value="{{ $art->sortierung }}">
                            <label class="flex items-center gap-1 text-xs"><input type="checkbox" name="aktiv" value="1" @checked($art->aktiv)> aktiv</label>
                            <button class="text-sm text-marke-700 hover:underline">Speichern</button>
                        </form>
                    @endforeach
                </div>
                <form method="post" action="{{ route('admin.vermerk-arten.store') }}" class="mt-4 flex flex-wrap items-center gap-2 border-t border-stone-100 pt-3">
                    @csrf
                    <input name="name" class="feld min-w-0 flex-1 py-1 text-sm" placeholder="Neue Art" required>
                    <input name="punkte" type="number" min="0" max="20" value="1" class="feld w-16 py-1 text-sm" required aria-label="Punkte">
                    <x-ui.knopf groesse="klein" art="sekundaer">Anlegen</x-ui.knopf>
                </form>
                <p class="mt-2 text-xs text-stone-500">Punkte gelten für neue Vermerke. Bereits erfasste behalten ihre Punkte.</p>
            </x-ui.karte>
        </div>
    </div>
</x-layouts.admin>
