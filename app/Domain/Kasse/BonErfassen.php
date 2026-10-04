<?php

namespace App\Domain\Kasse;

use App\Models\Boerse;
use App\Models\Bon;
use App\Models\Kassenschicht;
use App\Models\Teilnahme;
use App\Models\WarenkorbPosition;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Speichert einen Bon, den eine Kasse (ggf. offline) erzeugt hat.
 * Idempotent: Wird derselbe Bon (gleiche UUID) erneut gesendet, entsteht kein Duplikat.
 */
class BonErfassen
{
    /**
     * @param  array{uuid:string, erstellt_am:string, positionen: array<int, array{nummer:int, artikel:int, preis_cent:int, uuid?:string}>}  $daten
     */
    public function __invoke(Boerse $boerse, ?Kassenschicht $schicht, array $daten): Bon
    {
        $vorhanden = Bon::query()->where('uuid', $daten['uuid'])->first();
        if ($vorhanden) {
            return $vorhanden;
        }

        $teilnahmen = $boerse->teilnahmen()->mitNummer()->get()->keyBy('nummer');

        $positionen = collect($daten['positionen'])->map(function (array $position) use ($teilnahmen) {
            /** @var Teilnahme|null $teilnahme */
            $teilnahme = $teilnahmen->get((int) $position['nummer']);
            if (! $teilnahme) {
                throw new DomainException("Verkäufernummer {$position['nummer']} ist bei dieser Börse nicht vergeben.");
            }
            if ((int) $position['preis_cent'] <= 0) {
                throw new DomainException('Der Preis muss größer als 0 sein.');
            }

            return [
                'uuid' => $position['uuid'] ?? null,
                'teilnahme_id' => $teilnahme->id,
                'artikel_id' => $teilnahme->artikel()->where('laufnummer', $position['artikel'])->value('id'),
                'artikelnummer' => (int) $position['artikel'],
                'preis_cent' => (int) $position['preis_cent'],
            ];
        });

        if ($positionen->isEmpty()) {
            throw new DomainException('Der Bon enthält keine Artikel.');
        }

        return DB::transaction(function () use ($boerse, $schicht, $daten, $positionen) {
            $bon = Bon::create([
                'uuid' => $daten['uuid'],
                'boerse_id' => $boerse->id,
                'kassenschicht_id' => $schicht?->id,
                'summe_cent' => $positionen->sum('preis_cent'),
                'zahlart' => 'bar',
                'erstellt_am_geraet' => CarbonImmutable::parse($daten['erstellt_am'])->setTimezone(config('app.timezone')),
            ]);
            $bon->positionen()->createMany($positionen->all());

            // Diese Artikel sind bezahlt – aus dem gemeinsamen Warenkorb entfernen (auch nach Offline-Verkauf).
            WarenkorbPosition::query()->whereIn('uuid', $positionen->pluck('uuid')->filter())->delete();

            return $bon;
        });
    }
}
