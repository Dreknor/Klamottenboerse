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
    /**
     * Standard-Mailplan: Vorlage, Zielgruppe, Bezugsdatum, Versatz in Tagen – in zeitlicher Reihenfolge.
     * Ergänzt die Mails, die das System bei Ereignissen selbst verschickt (Anmeldung, Nummer, Warteliste,
     * Absage, Schicht, Ergebnis), sodass Interessenten, Verkäufer und Helfer von der Ankündigung bis
     * zum Feedback durchgehend begleitet werden.
     */
    public const STANDARD_MAILPLAN = [
        // Vorlauf: ankündigen und Anmeldung öffnen
        ['anmeldung_vorankuendigung', Zielgruppe::Interessenten, Bezugsdatum::Anmeldung, -14],
        ['anmeldung_moeglich', Zielgruppe::InteressentenKinderhaus, Bezugsdatum::AnmeldungKinderhaus, 0],
        ['anmeldung_moeglich', Zielgruppe::Interessenten, Bezugsdatum::Anmeldung, 0],
        // Vorbereitung: Helfer finden, Verkäufer erinnern, Warteliste ehrlich informieren
        ['helfer_gesucht', Zielgruppe::Verkaeufer, Bezugsdatum::Verkaufstag, -21],
        ['erinnerung_verkaeufer', Zielgruppe::Verkaeufer, Bezugsdatum::Anlieferung, -7],
        ['warteliste_stand', Zielgruppe::Warteliste, Bezugsdatum::Anlieferung, -3],
        ['erinnerung_helfer', Zielgruppe::Helfer, Bezugsdatum::Verkaufstag, -2],
        ['annahme_morgen', Zielgruppe::Verkaeufer, Bezugsdatum::Anlieferung, -1],
        // Verkaufstag und danach (das Ergebnis geht bei der Freigabe der Abrechnung raus)
        ['abholung_heute', Zielgruppe::Verkaeufer, Bezugsdatum::Verkaufstag, 0],
        ['feedback', Zielgruppe::VerkaeuferUndHelfer, Bezugsdatum::Verkaufstag, 2],
    ];

    /** @param  array<string, mixed>  $daten */
    public function __invoke(array $daten, bool $mitStandardMailplan = true, bool $mitCheckliste = true): Boerse
    {
        return DB::transaction(function () use ($daten, $mitStandardMailplan, $mitCheckliste) {
            // refresh(): Standardwerte der Datenbank (Nummernbereich, Blockgröße …) laden
            $boerse = Boerse::create($daten + ['status' => BoerseStatus::Planung])->refresh();

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

    /**
     * Legt den Standard-Mailplan an – oder stellt ihn bei einer bestehenden Börse wieder her: Fehlende
     * Mails kommen dazu, noch nicht verschickte bekommen wieder Standardtermin und werden aktiviert.
     * Verschickte und eigene Einträge bleiben unverändert.
     *
     * @return int Anzahl ergänzter oder zurückgesetzter Einträge
     */
    public function standardMailplan(Boerse $boerse): int
    {
        $anzahl = 0;

        foreach (self::STANDARD_MAILPLAN as [$schluessel, $zielgruppe, $bezugsdatum, $versatz]) {
            $vorlage = Mailvorlage::query()->where('schluessel', $schluessel)->first();
            if (! $vorlage) {
                continue;
            }

            $standard = ['bezugsdatum' => $bezugsdatum, 'versatz_tage' => $versatz, 'aktiv' => true];
            $eintrag = $boerse->mailplan()->firstOrNew(['mailvorlage_id' => $vorlage->id, 'zielgruppe' => $zielgruppe]);

            if ($eintrag->eingeplant_at) {
                continue;
            }

            $eintrag->fill($standard);
            if ($eintrag->isDirty()) {
                $eintrag->save();
                $anzahl++;
            }
        }

        return $anzahl;
    }
}
