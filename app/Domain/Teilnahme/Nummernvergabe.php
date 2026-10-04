<?php

namespace App\Domain\Teilnahme;

use App\Models\Boerse;
use App\Models\Nummernreservierung;
use App\Models\Person;
use Illuminate\Support\Collection;

/**
 * Ermittelt die Verkäufernummer für eine Anmeldung.
 *
 * Regel (in dieser Reihenfolge):
 *  1. Feste Reservierung der Person → diese Nummer.
 *  2. Sonst die zuletzt genutzte Nummer, wenn frei und ihr Block den Zielwert + Toleranz nicht überschreitet.
 *  3. Sonst die niedrigste freie Nummer im Block mit den wenigsten belegten Nummern.
 * Reservierungen anderer Personen und die Kinderhaus-Nummer sind nie frei.
 */
class Nummernvergabe
{
    public function __construct(private readonly Boerse $boerse) {}

    public function naechsteNummer(?Person $person): ?int
    {
        $belegt = $this->belegteNummern();
        $reservierungen = $this->reservierungen();

        if ($person) {
            $eigene = $reservierungen->firstWhere('person_id', $person->id);
            if ($eigene && ! $belegt->contains($eigene->nummer)) {
                return $eigene->nummer;
            }
        }

        $gesperrt = $reservierungen
            ->when($person, fn ($r) => $r->where('person_id', '!=', $person->id))
            ->pluck('nummer')
            ->push($this->boerse->kinderhaus_nummer);

        $istFrei = fn (int $nummer) => $nummer >= $this->boerse->nummer_von
            && $nummer <= $this->boerse->nummer_bis
            && ! $belegt->contains($nummer)
            && ! $gesperrt->contains($nummer);

        $belegung = $this->blockbelegung();

        $letzte = $person?->letzteNummer($this->boerse);
        if ($letzte !== null && $istFrei($letzte)) {
            $block = $this->boerse->blockVon($letzte);
            if (($belegung[$block] ?? 0) < $this->boerse->zielProBlock() + $this->boerse->block_toleranz) {
                return $letzte;
            }
        }

        $bloecke = $this->boerse->bloecke();
        uksort($bloecke, fn ($a, $b) => [$belegung[$a], $a] <=> [$belegung[$b], $b]);

        // Im schwächsten 100er-Block den schwächsten Zehner wählen, dort die niedrigste freie Nummer.
        foreach (array_keys($bloecke) as $blockStart) {
            $zehnerBelegung = $this->zehnerbelegung($blockStart);
            $zehner = $this->boerse->zehner($blockStart);
            uksort($zehner, fn ($a, $b) => [$zehnerBelegung[$a], $a] <=> [$zehnerBelegung[$b], $b]);

            foreach ($zehner as [$von, $bis]) {
                for ($nummer = $von; $nummer <= $bis; $nummer++) {
                    if ($istFrei($nummer)) {
                        return $nummer;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Belegte Nummern je Zehner eines Blocks; noch nicht genutzte Reservierungen zählen mit.
     *
     * @return array<int, int> Zehnerstart => Anzahl
     */
    public function zehnerbelegung(int $blockStart): array
    {
        $belegung = array_fill_keys(array_keys($this->boerse->zehner($blockStart)), 0);

        foreach ($this->alleBelegten() as $nummer) {
            $zehner = intdiv($nummer, 10) * 10;
            if (array_key_exists($zehner, $belegung) && $this->boerse->blockVon($nummer) === $blockStart) {
                $belegung[$zehner]++;
            }
        }

        return $belegung;
    }

    /** @return Collection<int, int> Belegte und reservierte Nummern ohne Kinderhaus-Nummer */
    private function alleBelegten(): Collection
    {
        return $this->belegteNummern()
            ->merge($this->reservierungen()->pluck('nummer'))
            ->unique()
            ->reject(fn ($n) => $n === $this->boerse->kinderhaus_nummer);
    }

    /** Hat die Person (noch) Anspruch auf einen Platz? Reservierte Personen immer. */
    public function hatPlatzFuer(?Person $person): bool
    {
        if ($person && $this->reservierungen()->contains('person_id', $person->id)) {
            return true;
        }

        $offeneReservierungen = $this->reservierungen()
            ->reject(fn ($r) => $this->belegteNummern()->contains($r->nummer))
            ->count();

        return $this->boerse->belegteNummern() + $offeneReservierungen < $this->boerse->kapazitaet;
    }

    /**
     * Belegte Nummern je Block; noch nicht genutzte Reservierungen zählen mit.
     *
     * @return array<int, int> Blockstart => Anzahl
     */
    public function blockbelegung(): array
    {
        $belegung = array_fill_keys(array_keys($this->boerse->bloecke()), 0);

        $this->alleBelegten()
            ->each(function (int $nummer) use (&$belegung) {
                $block = $this->boerse->blockVon($nummer);
                if ($block !== null) {
                    $belegung[$block]++;
                }
            });

        return $belegung;
    }

    /** @return Collection<int, int> */
    private function belegteNummern(): Collection
    {
        return $this->boerse->teilnahmen()->mitNummer()->pluck('nummer')->map(fn ($n) => (int) $n);
    }

    /** @return Collection<int, Nummernreservierung> */
    private function reservierungen(): Collection
    {
        return Nummernreservierung::query()->gueltigFuer($this->boerse)->get();
    }
}
