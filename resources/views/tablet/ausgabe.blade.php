@php use App\Support\Geld; @endphp
<x-layouts.tablet vermerk="ausgabe" titel="Ausgabe" :zurueck="route('tablet.index')">
    <p class="mb-3 text-stone-600">{{ $ausgezahlt }} ausgegeben · {{ $offen }} warten noch</p>

    @include('tablet._suche', ['label' => 'Nummer (Kistenzettel scannen) oder Name'])

    @foreach ($treffer as $t)
        <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-4xl font-bold">{{ $t->nummer }}</p>
                    <p class="text-xl">{{ $t->anzeigeName() }}</p>
                    <p class="text-stone-500">{{ $t->kisten->count() }} Kiste(n)</p>
                </div>
                @if ($t->abrechnung)
                    <div class="text-right">
                        <p class="text-stone-500">Auszahlen</p>
                        <p class="text-5xl font-bold text-emerald-700">{{ Geld::format($t->abrechnung->auszahlung_cent) }}</p>
                        <p class="text-sm text-stone-500">{{ $t->abrechnung->verkaufte_artikel }} Teile · Umsatz {{ Geld::format($t->abrechnung->umsatz_cent) }} · Spende {{ Geld::format($t->abrechnung->spende_cent) }}</p>
                    </div>
                @endif
            </div>

            @if (! $t->abrechnung)
                <p class="mt-4 rounded-xl bg-amber-100 p-4 text-amber-900">Noch nicht abgerechnet – bitte Orga-Team Bescheid geben.</p>
            @elseif ($t->abrechnung->ausgezahlt_at)
                <p class="mt-4 rounded-xl bg-emerald-100 p-4 text-emerald-900">Bereits ausgegeben um {{ $t->abrechnung->ausgezahlt_at->format('H:i') }} Uhr.</p>
            @elseif ($t->ist_kinderhaus)
                <p class="mt-4 rounded-xl bg-sky-100 p-4 text-sky-900">Kinderhaus – keine Auszahlung, Erlös bleibt in der Kasse.</p>
            @else
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($stueckelung[$t->id] as $wert => $anzahl)
                        <span class="rounded-lg bg-stone-100 px-3 py-2 text-lg">{{ $anzahl }} × {{ Geld::format($wert) }}</span>
                    @endforeach
                </div>
                <form method="post" action="{{ route('tablet.auszahlen', $t) }}" class="mt-4" onsubmit="return confirm('Kiste(n) und {{ Geld::format($t->abrechnung->auszahlung_cent) }} an {{ $t->anzeigeName() }} ausgegeben?')">
                    @csrf
                    <button class="w-full rounded-xl bg-emerald-600 py-5 text-xl font-semibold text-white">Kiste und Geld ausgegeben ✓</button>
                </form>
            @endif
        </div>
    @endforeach

    @if (request('suche') && $treffer->isEmpty())
        <p class="mt-4 rounded-xl bg-amber-100 p-4 text-amber-900">Nichts gefunden.</p>
    @endif
</x-layouts.tablet>
