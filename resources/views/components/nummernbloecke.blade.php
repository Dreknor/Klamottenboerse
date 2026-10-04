@props(['boerse'])
@php
    $vergabe = new \App\Domain\Teilnahme\Nummernvergabe($boerse);
    $belegung = $vergabe->blockbelegung();
    $ziel = $boerse->zielProBlock();
    $zielZehner = max(1, (int) ceil($ziel / 10));
@endphp
<div class="space-y-4">
    <p class="text-sm text-stone-500">Ziel: {{ $ziel }} je 100er-Block (± {{ $boerse->block_toleranz }}), etwa {{ $zielZehner }} je Zehner</p>
    @foreach ($boerse->bloecke() as $start => [$von, $bis])
        @php $anzahl = $belegung[$start] ?? 0; $zehner = $vergabe->zehnerbelegung($start); @endphp
        <div>
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium">{{ $start }}er <span class="font-normal text-stone-500">({{ $von }}–{{ $bis }})</span></span>
                <x-ui.abzeichen :farbe="abs($anzahl - $ziel) <= $boerse->block_toleranz ? 'emerald' : 'amber'">{{ $anzahl }} / {{ $ziel }}</x-ui.abzeichen>
            </div>
            <div class="mt-1 grid grid-cols-10 gap-0.5" role="list" aria-label="Belegung je Zehner im {{ $start }}er-Block">
                @foreach ($zehner as $zStart => $zAnzahl)
                    @php
                        $farbe = match (true) {
                            $zAnzahl === 0 => 'bg-stone-100 text-stone-400',
                            $zAnzahl > $zielZehner => 'bg-amber-200 text-amber-900',
                            default => 'bg-emerald-200 text-emerald-900',
                        };
                    @endphp
                    <span role="listitem" class="rounded px-0.5 py-1 text-center text-[11px] leading-none {{ $farbe }}" title="{{ $zStart }}–{{ $zStart + 9 }}: {{ $zAnzahl }} belegt">
                        <span class="block opacity-70">{{ substr((string) $zStart, -2, 1) }}x</span>{{ $zAnzahl }}
                    </span>
                @endforeach
            </div>
        </div>
    @endforeach
    <p class="text-xs text-stone-500">Neue Nummern gehen automatisch in den schwächsten Block und dort in den schwächsten Zehner. Nummer {{ $boerse->kinderhaus_nummer }}: Kinderhaus (fest, ohne Spende).</p>
</div>
