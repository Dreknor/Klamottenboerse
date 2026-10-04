<?php

namespace App\Domain\Boersen\Actions;

use App\Enums\Bezugsdatum;
use App\Enums\BoerseStatus;
use App\Enums\TeilnahmeStatus;
use App\Enums\Zielgruppe;
use App\Models\Boerse;
use App\Models\Checklistenvorlage;
use App\Models\Mailvorlage;
use Illuminate\Support\Facades\DB;

/**
 * Legt eine Börse an und richtet alles ein, was jede Börse braucht:
 * die Kinderhaus-Nummer, die Checkliste aus der Vorlage und den Standard-Mailplan.
 */
class BoerseAnlegen
{
    /** Standard-Mailplan: Vorlage, Zielgruppe, Bezugsdatum, Versatz in Tagen. */
    public const STANDARD_MAILPLAN = [
        ['anmeldung_moeglich', Zielgruppe::InteressentenKinderhaus, Bezugsdatum::AnmeldungKinderhaus, 0],
        ['anmeldung_moeglich', Zielgruppe::Interessenten, Bezugsdatum::Anmeldung, 0],
        ['erinnerung_verkaeufer', Zielgruppe::Verkaeufer, Bezugsdatum::Anlieferung, -7],
        ['erinnerung_helfer', Zielgruppe::Helfer, Bezugsdatum::Verkaufstag, -2],
        ['feedback', Zielgruppe::VerkaeuferUndHelfer, Bezugsdatum::Verkaufstag, 2],
    ];

    /** @param  array<string, mixed>  $daten */
    public function __invoke(array $daten, bool $mitStandardMailplan = true, bool $mitCheckliste = true): Boerse
    {
        return DB::transaction(function () use ($daten, $mitStandardMailplan, $mitCheckliste) {
            $boerse = Boerse::create($daten + ['status' => BoerseStatus::Planung]);

            $this->kinderhausNummerAnlegen($boerse);

            if ($mitCheckliste) {
                $vorlage = Checklistenvorlage::query()->where('fuer_neue_boersen', true)->first();
                if ($vorlage) {
                    (new ChecklisteErzeugen)($boerse, $vorlage);
                }
            }

            if ($mitStandardMailplan) {
                $this->standardMailplan($boerse);
            }

            activity()->performedOn($boerse)->log('Börse angelegt');

            return $boerse;
        });
    }

    /** Die Kinderhaus-Nummer wird automatisch erfasst und ohne Spende abgerechnet. */
    public function kinderhausNummerAnlegen(Boerse $boerse): void
    {
        $boerse->teilnahmen()->firstOrCreate(
            ['ist_kinderhaus' => true],
            [
                'nummer' => $boerse->kinderhaus_nummer,
                'status' => TeilnahmeStatus::Zugeteilt,
                'spendenfrei' => true,
                'quelle' => 'automatisch',
                'zugeteilt_at' => now(),
            ],
        );
    }

    private function standardMailplan(Boerse $boerse): void
    {
        foreach (self::STANDARD_MAILPLAN as [$schluessel, $zielgruppe, $bezugsdatum, $versatz]) {
            $vorlage = Mailvorlage::query()->where('schluessel', $schluessel)->first();
            if (! $vorlage) {
                continue;
            }

            $boerse->mailplan()->create([
                'mailvorlage_id' => $vorlage->id,
                'zielgruppe' => $zielgruppe,
                'bezugsdatum' => $bezugsdatum,
                'versatz_tage' => $versatz,
                'aktiv' => true,
            ]);
        }
    }
}
