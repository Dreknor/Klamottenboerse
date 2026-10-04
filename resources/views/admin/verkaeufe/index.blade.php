@php use App\Support\Geld; @endphp
<x-layouts.admin titel="Verkäufe & Stornos">
    <x-ui.kopf titel="Verkäufe & Stornos" :unter="$boerse->titel.' · '.$bons->total().' Einkäufe'" />

    <x-ui.karte>
        <form method="get" class="mb-4 flex flex-wrap items-center gap-3">
            <input name="nummer" type="number" value="{{ $nummer }}" class="feld max-w-40" placeholder="Verkäufernummer">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="stornos" value="1" @checked(request()->boolean('stornos')) onchange="this.form.submit()"> nur Stornos</label>
            <x-ui.knopf art="sekundaer">Filtern</x-ui.knopf>
        </form>

        @forelse ($bons as $bon)
            <div class="border-b border-stone-100 py-3 last:border-0" x-data="{ offen: false }">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <button type="button" class="text-left" @click="offen = !offen">
                        <span class="font-medium {{ $bon->storniert_at ? 'text-stone-400 line-through' : '' }}">{{ Geld::format($bon->summe_cent) }}</span>
                        <span class="text-sm text-stone-500">· {{ $bon->positionen->count() }} Teile · {{ $bon->erstellt_am_geraet->format('d.m. H:i') }}
                            · {{ $bon->kassenschicht?->kasse?->name ?? 'Kasse ?' }}{{ $bon->kassenschicht?->person ? ', '.$bon->kassenschicht->person->vorname : '' }}</span>
                        @if ($bon->storniert_at)<x-ui.abzeichen farbe="red">storniert: {{ $bon->storno_grund }}</x-ui.abzeichen>@endif
                    </button>
                    @if ($bon->storniert_at)
                        <form method="post" action="{{ route('admin.verkaeufe.zuruecknehmen', $bon) }}">@csrf @method('delete')<button class="text-sm text-marke-700 hover:underline">Storno zurücknehmen</button></form>
                    @else
                        <form method="post" action="{{ route('admin.verkaeufe.bon', $bon) }}" class="flex items-center gap-2" onsubmit="return confirm('Ganzen Einkauf stornieren?')">
                            @csrf
                            <input name="grund" class="feld w-48 py-1 text-sm" placeholder="Grund" required>
                            <button class="text-sm text-red-700 hover:underline">Ganzen Bon stornieren</button>
                        </form>
                    @endif
                </div>
                <table x-show="offen" x-cloak class="tabelle mt-2">
                    <tbody>
                    @foreach ($bon->positionen as $p)
                        <tr class="{{ $p->storniert_at ? 'text-stone-400' : '' }}">
                            <td class="font-mono">{{ $p->teilnahme->nummer }}-{{ $p->artikelnummer }}</td>
                            <td class="text-right {{ $p->storniert_at ? 'line-through' : '' }}">{{ Geld::format($p->preis_cent) }}</td>
                            <td class="text-right">
                                @if ($p->storniert_at)
                                    storniert: {{ $p->storno_grund }}
                                @elseif (! $bon->storniert_at)
                                    <form method="post" action="{{ route('admin.verkaeufe.position', $p) }}" class="flex justify-end gap-2">
                                        @csrf
                                        <input name="grund" class="feld w-40 py-1 text-sm" placeholder="Grund" required>
                                        <button class="text-sm text-red-700 hover:underline">stornieren</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p class="text-stone-500">Keine Verkäufe gefunden.</p>
        @endforelse
        <div class="mt-4">{{ $bons->links() }}</div>
    </x-ui.karte>
    <p class="mt-3 text-sm text-stone-500">Nach einem Storno wird eine vorhandene Abrechnung automatisch neu berechnet. Bereits ausgezahlte Verkäufe können nicht storniert werden.</p>
</x-layouts.admin>
