<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Models\Teilnahme;
use DomainException;

/** Umverteilen für den Blockausgleich: Orga gibt einer Teilnahme eine andere freie Nummer. */
class NummerAendern
{
    public function __invoke(Teilnahme $teilnahme, int $neueNummer, bool $mailSenden = true): void
    {
        $boerse = $teilnahme->boerse;

        if ($teilnahme->ist_kinderhaus) {
            throw new DomainException('Die Kinderhaus-Nummer ist fest.');
        }
        if ($neueNummer < $boerse->nummer_von || $neueNummer > $boerse->nummer_bis) {
            throw new DomainException("Die Nummer muss zwischen {$boerse->nummer_von} und {$boerse->nummer_bis} liegen.");
        }
        if ($boerse->teilnahmen()->mitNummer()->where('nummer', $neueNummer)->exists()) {
            throw new DomainException("Die Nummer {$neueNummer} ist bereits vergeben.");
        }
        if ($teilnahme->bonpositionen()->exists()) {
            throw new DomainException('Für diese Nummer gibt es bereits Verkäufe – sie kann nicht mehr geändert werden.');
        }

        $alt = $teilnahme->nummer;
        $teilnahme->update(['nummer' => $neueNummer]);

        activity()->performedOn($teilnahme)->withProperties(['alt' => $alt, 'neu' => $neueNummer])->log('Nummer geändert');

        if ($mailSenden && $teilnahme->person) {
            Postausgang::einplanen($teilnahme->person, 'nummer_geaendert', $boerse, ['alte_nummer' => (string) $alt]);
        }
    }
}
