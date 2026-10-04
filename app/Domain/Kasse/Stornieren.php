<?php

namespace App\Domain\Kasse;

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Models\Abrechnung;
use App\Models\Boerse;
use App\Models\Bon;
use App\Models\Bonposition;
use DomainException;

/**
 * Storno eines ganzen Bons oder einer einzelnen Position – mit Grund im Protokoll.
 * Ist schon abgerechnet, wird die Abrechnung neu berechnet (bereits ausgezahlte bleiben unverändert).
 */
class Stornieren
{
    public function __construct(private readonly AbrechnungBerechnen $berechnen) {}

    public function bon(Bon $bon, string $grund): void
    {
        $this->pruefen($bon->positionen()->pluck('teilnahme_id')->all());
        $bon->update(['storniert_at' => now(), 'storno_grund' => $grund]);
        activity()->performedOn($bon)->withProperties(['grund' => $grund, 'summe_cent' => $bon->summe_cent])->log('Bon storniert');
        $this->neuBerechnen($bon->boerse);
    }

    public function position(Bonposition $position, string $grund): void
    {
        $this->pruefen([$position->teilnahme_id]);
        $position->update(['storniert_at' => now(), 'storno_grund' => $grund]);
        $bon = $position->bon;
        $bon->update(['summe_cent' => $bon->positionen()->whereNull('storniert_at')->sum('preis_cent')]);
        activity()->performedOn($bon)->withProperties(['grund' => $grund, 'artikel' => $position->artikelnummer, 'preis_cent' => $position->preis_cent])->log('Position storniert');
        $this->neuBerechnen($bon->boerse);
    }

    public function zuruecknehmen(Bon $bon): void
    {
        $this->pruefen($bon->positionen()->pluck('teilnahme_id')->all());
        $bon->update(['storniert_at' => null, 'storno_grund' => null]);
        activity()->performedOn($bon)->log('Bon-Storno zurückgenommen');
        $this->neuBerechnen($bon->boerse);
    }

    /** @param  list<int>  $teilnahmeIds */
    private function pruefen(array $teilnahmeIds): void
    {
        if (Abrechnung::query()->whereIn('teilnahme_id', $teilnahmeIds)->whereNotNull('ausgezahlt_at')->exists()) {
            throw new DomainException('Für diesen Verkauf wurde schon ausgezahlt – ein Storno würde die Auszahlung verfälschen.');
        }
    }

    private function neuBerechnen(Boerse $boerse): void
    {
        if (Abrechnung::query()->whereHas('teilnahme', fn ($q) => $q->where('boerse_id', $boerse->id))->exists()) {
            ($this->berechnen)($boerse);
        }
    }
}
