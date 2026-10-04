<x-layouts.tablet titel="Rückpacken" :zurueck="route('tablet.index')">
    @include('tablet._suche', ['label' => 'Verkäufernummer', 'name' => 'nummer', 'inputmode' => 'numeric'])

    @if ($teilnahme)
        <div class="mt-4 rounded-2xl bg-white p-5 shadow-sm">
            <p class="text-4xl font-bold">{{ $teilnahme->nummer }} <span class="text-xl font-normal">{{ $teilnahme->anzeigeName() }}</span></p>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl bg-emerald-50 p-4">
                    <p class="font-semibold text-emerald-900">Verkauft ({{ $verkauft->count() }}) – nicht mehr in der Kiste</p>
                    <p class="mt-2 font-mono text-xl leading-relaxed">{{ $verkauft->implode(', ') ?: '–' }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-4">
                    @if ($teilnahme->artikel->isNotEmpty())
                        <p class="font-semibold text-amber-900">Zurück in die Kiste ({{ $zurueck->count() }})</p>
                        <ul class="mt-2 space-y-1">
                            @foreach ($zurueck as $a)
                                <li><span class="font-mono font-semibold">{{ $a->laufnummer }}</span> {{ $a->beschreibung }} {{ $a->groesse ? 'Gr. '.$a->groesse : '' }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="font-semibold text-amber-900">Handschriftliche Etiketten</p>
                        <p class="mt-2">Alle Teile mit dieser Nummer, deren Artikelnummer links nicht steht, kommen zurück in die Kiste.</p>
                    @endif
                </div>
            </div>
        </div>
    @elseif (request('nummer'))
        <p class="mt-4 rounded-xl bg-amber-100 p-4 text-amber-900">Nummer nicht gefunden.</p>
    @endif
</x-layouts.tablet>
