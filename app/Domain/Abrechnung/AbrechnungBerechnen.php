<?php

namespace App\Domain\Abrechnung;

use App\Enums\TeilnahmeStatus;
use App\Models\Abrechnung;
use App\Models\Boerse;
use App\Models\Teilnahme;
use App\Support\Geld;
use Illuminate\Support\Facades\DB;

/**
 * Rechnet je Verkäufer: Umsatz aus gültigen Bonpositionen, Spende (Provision), Auszahlung.
 * Die Auszahlung wird auf rundung_cent abgerundet; der Rundungsrest zählt zur Spende.
 * Die Kinderhaus-Nummer (spendenfrei) behält den vollen Erlös.
 * Bereits ausgezahlte Abrechnungen werden nicht mehr verändert.
 */
class AbrechnungBerechnen
{
    /** @return array{umsatz:int, spende:int, auszahlung:int} */
    public static function rechnen(int $umsatzCent, Boerse $boerse, bool $spendenfrei): array
    {
        if ($spendenfrei) {
            return ['umsatz' => $umsatzCent, 'spende' => 0, 'auszahlung' => $umsatzCent];
        }

        $nachSpende = $umsatzCent - Geld::promille($umsatzCent, $boerse->provision_promille);
        $auszahlung = Geld::abrunden($nachSpende, $boerse->rundung_cent);

        return ['umsatz' => $umsatzCent, 'spende' => $umsatzCent - $auszahlung, 'auszahlung' => $auszahlung];
    }

    public function __invoke(Boerse $boerse): int
    {
        $umsaetze = DB::table('bonpositionen')
            ->join('bons', 'bons.id', '=', 'bonpositionen.bon_id')
            ->where('bons.boerse_id', $boerse->id)
            ->whereNull('bons.storniert_at')
            ->whereNull('bonpositionen.storniert_at')
            ->groupBy('bonpositionen.teilnahme_id')
            ->selectRaw('bonpositionen.teilnahme_id, sum(bonpositionen.preis_cent) as umsatz, count(*) as anzahl')
            ->get()
            ->keyBy('teilnahme_id');

        $teilnahmen = $boerse->teilnahmen()
            ->whereIn('status', [TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Angeliefert, TeilnahmeStatus::Abgerechnet])
            ->with('abrechnung')
            ->get();

        $anzahl = 0;
        foreach ($teilnahmen as $teilnahme) {
            /** @var Teilnahme $teilnahme */
            if ($teilnahme->abrechnung?->ausgezahlt_at) {
                continue;
            }

            $zeile = $umsaetze->get($teilnahme->id);
            $betraege = self::rechnen((int) ($zeile->umsatz ?? 0), $boerse, $teilnahme->spendenfrei);

            Abrechnung::updateOrCreate(['teilnahme_id' => $teilnahme->id], [
                'umsatz_cent' => $betraege['umsatz'],
                'spende_cent' => $betraege['spende'],
                'auszahlung_cent' => $betraege['auszahlung'],
                'verkaufte_artikel' => (int) ($zeile->anzahl ?? 0),
                'berechnet_at' => now(),
            ]);

            // Wer nichts angeliefert hat, wird nicht als "abgerechnet" markiert.
            if ($teilnahme->status === TeilnahmeStatus::Angeliefert || $zeile || $teilnahme->ist_kinderhaus) {
                $teilnahme->update(['status' => TeilnahmeStatus::Abgerechnet]);
            }
            $anzahl++;
        }

        activity()->performedOn($boerse)->withProperties(['anzahl' => $anzahl])->log('Abrechnung berechnet');

        return $anzahl;
    }
}
