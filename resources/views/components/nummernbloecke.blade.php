@props(['boerse'])
@php
    $vergabe = new \App\Domain\Teilnahme\Nummernvergabe($boerse);
    $belegung = $vergabe->blockbelegung();
    $bloecke = $boerse->bloecke();
    $ziel = $boerse->zielProBlock();
    $toleranz = $boerse->block_toleranz;
    // Ausgleich: Vergleich mit dem Durchschnitt aller Blöcke (nicht mit dem Endziel – das ist erst am Ende erreicht)
    $schnitt = count($bloecke) ? array_sum(array_map(fn ($s) => $belegung[$s] ?? 0, array_keys($bloecke))) / count($bloecke) : 0;
    $zielZehner = max(1, (int) ceil($ziel / 10));
    $beispiel = array_key_first($bloecke) ?? 200;
@endphp
<div class="space-y-4">
    @foreach ($bloecke as $start => [$von, $bis])
        @php
            $anzahl = $belegung[$start] ?? 0;
            $zehner = $vergabe->zehnerbelegung($start);
            $abweichung = $anzahl - $schnitt;
            [$farbe, $hinweis] = match (true) {
                $abweichung > $toleranz => ['amber', 'deutlich voller als die anderen'],
                $abweichung < -$toleranz => ['red', 'deutlich leerer als die anderen'],
                default => ['emerald', 'ausgeglichen'],
            };
        @endphp
        <div>
            <div class="flex items-center justify-between text-sm">
                <span class="font-medium">{{ $start }}er <span class="font-normal text-stone-500">({{ $von }}–{{ $bis }})</span></span>
                <span class="flex items-center gap-2">
                    <span class="text-xs text-stone-500">{{ $hinweis }}</span>
                    <x-ui.abzeichen :farbe="$farbe" class="whitespace-nowrap">{{ $anzahl }} / {{ $ziel }}</x-ui.abzeichen>
                </span>
            </div>
            <div class="mt-1 h-1.5 overflow-hidden rounded bg-stone-100" aria-hidden="true">
                <div class="h-full bg-marke-500" style="width: {{ min(100, $ziel ? round($anzahl / $ziel * 100) : 0) }}%"></div>
            </div>
            <div class="mt-1 grid grid-cols-10 gap-0.5" role="list" aria-label="Belegung je Zehner im {{ $start }}er-Block">
                @foreach ($zehner as $zStart => $zAnzahl)
                    @php
                        $zFarbe = match (true) {
                            $zAnzahl === 0 => 'bg-stone-100 text-stone-400',
                            $zAnzahl > $zielZehner => 'bg-amber-200 text-amber-900',
                            default => 'bg-emerald-200 text-emerald-900',
                        };
                    @endphp
                    <span role="listitem" class="rounded px-0.5 py-1 text-center text-[11px] leading-none {{ $zFarbe }}" title="{{ $zStart }}–{{ $zStart + 9 }}: {{ $zAnzahl }} belegt">
                        <span class="block opacity-70">{{ substr((string) $zStart, -2, 1) }}x</span>{{ $zAnzahl }}
                    </span>
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="space-y-1 border-t border-stone-100 pt-3 text-xs text-stone-600">
        <p><strong>Zahl rechts</strong> = vergebene und reservierte Nummern / geplant je Block ({{ $boerse->kapazitaet }} Plätze ÷ {{ count($bloecke) }} Blöcke ≈ {{ $ziel }}). Der Balken zeigt, wie voll der Block schon ist.</p>
        <p>
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span> ausgeglichen (höchstens {{ $toleranz }} Nummern vom Durchschnitt der Blöcke entfernt) ·
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-amber-500"></span> deutlich voller ·
            <span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span> deutlich leerer als die anderen Blöcke
        </p>
        <p>
            <strong>Kästchen 0x–9x</strong> = die zehn Zehner eines Blocks (z. B. 3x im {{ $beispiel }}er = {{ $beispiel + 30 }}–{{ $beispiel + 39 }}), die Zahl darunter = vergebene und reservierte Nummern:
            grau leer · grün bis {{ $zielZehner }} · gelb mehr als {{ $zielZehner }}.
        </p>
        <p>Neue Nummern gehen automatisch in den leersten Block und dort in den leersten Zehner. Nummer {{ $boerse->kinderhaus_nummer }}: Kinderhaus (fest, ohne Spende).</p>
    </div>
</div>
