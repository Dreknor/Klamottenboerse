<?php

namespace App\Domain\Boersen\Actions;

use App\Models\Boerse;
use App\Models\Checklistenvorlage;

/** Erzeugt die Aufgaben einer Börse aus einer Checklistenvorlage; Fälligkeit relativ zum Verkaufstag. */
class ChecklisteErzeugen
{
    public function __invoke(Boerse $boerse, Checklistenvorlage $vorlage): int
    {
        $anzahl = 0;

        foreach ($vorlage->eintraege as $eintrag) {
            $boerse->aufgaben()->create([
                'titel' => $eintrag->titel,
                'beschreibung' => $eintrag->beschreibung,
                'phase' => $eintrag->phase,
                'faellig_am' => $boerse->verkaufstag->copy()->addDays($eintrag->versatz_tage),
                'sortierung' => $eintrag->sortierung,
            ]);
            $anzahl++;
        }

        return $anzahl;
    }
}
