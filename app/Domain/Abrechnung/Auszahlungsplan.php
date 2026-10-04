<?php

namespace App\Domain\Abrechnung;

use App\Models\Abrechnung;
use App\Models\Boerse;
use App\Support\Geld;
use Illuminate\Support\Collection;

/**
 * Stückelung je Verkäufer und Summe aller benötigten Scheine/Münzen –
 * damit vor der Börse das passende Wechselgeld bei der Bank bestellt werden kann.
 */
class Auszahlungsplan
{
    /** @var Collection<int, array{abrechnung: Abrechnung, stueckelung: array<int,int>}> */
    public Collection $zeilen;

    /** @var array<int, int> Wert in Cent => Anzahl */
    public array $summe = [];

    public int $gesamtCent = 0;

    public function __construct(Boerse $boerse)
    {
        $abrechnungen = Abrechnung::query()
            ->whereHas('teilnahme', fn ($q) => $q->where('boerse_id', $boerse->id)->where('spendenfrei', false))
            ->where('auszahlung_cent', '>', 0)
            ->with('teilnahme.person')
            ->get()
            ->sortBy(fn (Abrechnung $a) => $a->teilnahme->nummer);

        $this->zeilen = $abrechnungen->map(function (Abrechnung $abrechnung) {
            $stueckelung = Geld::stueckelung($abrechnung->auszahlung_cent);
            foreach ($stueckelung as $wert => $anzahl) {
                $this->summe[$wert] = ($this->summe[$wert] ?? 0) + $anzahl;
            }
            $this->gesamtCent += $abrechnung->auszahlung_cent;

            return ['abrechnung' => $abrechnung, 'stueckelung' => $stueckelung];
        })->values();

        krsort($this->summe);
    }
}
