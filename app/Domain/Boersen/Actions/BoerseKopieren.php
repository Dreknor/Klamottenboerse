<?php

namespace App\Domain\Boersen\Actions;

use App\Models\Boerse;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Neue Börse aus einer bestehenden: Einstellungen, alle Termine, Schichten, Mailplan und
 * Checkliste werden übernommen und um den Abstand zum neuen Verkaufstag verschoben.
 */
class BoerseKopieren
{
    public function __construct(private readonly BoerseAnlegen $anlegen) {}

    public function __invoke(Boerse $vorlage, CarbonInterface $neuerVerkaufstag, ?string $titel = null): Boerse
    {
        return DB::transaction(function () use ($vorlage, $neuerVerkaufstag, $titel) {
            $tage = (int) $vorlage->verkaufstag->copy()->startOfDay()->diffInDays($neuerVerkaufstag->copy()->startOfDay(), false);

            $daten = $vorlage->only([
                'ort_id', 'nummer_von', 'nummer_bis', 'blockgroesse', 'block_toleranz', 'kapazitaet',
                'kinderhaus_nummer', 'max_teile', 'max_kisten', 'provision_promille', 'rundung_cent',
                'angebot_stunden', 'hinweise',
            ]);

            foreach (Boerse::TERMINFELDER as $feld) {
                $daten[$feld] = $vorlage->{$feld}?->copy()->addDays($tage);
            }

            $boerse = ($this->anlegen)(
                $daten + [
                    'titel' => $titel ?? 'Klamottenbörse '.$neuerVerkaufstag->format('d.m.Y'),
                    'verkaufstag' => $neuerVerkaufstag->toDateString(),
                ],
                mitStandardMailplan: $vorlage->mailplan()->doesntExist(),
                mitCheckliste: $vorlage->aufgaben()->doesntExist(),
            );

            foreach ($vorlage->mailplan as $eintrag) {
                $boerse->mailplan()->create($eintrag->only(['mailvorlage_id', 'zielgruppe', 'bezugsdatum', 'versatz_tage', 'aktiv']));
            }

            foreach ($vorlage->aufgaben as $aufgabe) {
                $boerse->aufgaben()->create([
                    'titel' => $aufgabe->titel,
                    'beschreibung' => $aufgabe->beschreibung,
                    'phase' => $aufgabe->phase,
                    'faellig_am' => $aufgabe->faellig_am?->copy()->addDays($tage),
                    'zustaendig_id' => $aufgabe->zustaendig_id,
                    'sortierung' => $aufgabe->sortierung,
                ]);
            }

            foreach ($vorlage->schichten as $schicht) {
                $boerse->schichten()->create([
                    'bereich' => $schicht->bereich,
                    'beschreibung' => $schicht->beschreibung,
                    'beginn' => $schicht->beginn->copy()->addDays($tage),
                    'ende' => $schicht->ende->copy()->addDays($tage),
                    'soll' => $schicht->soll,
                ]);
            }

            activity()->performedOn($boerse)->withProperties(['vorlage' => $vorlage->id])->log('Börse kopiert');

            return $boerse;
        });
    }
}
