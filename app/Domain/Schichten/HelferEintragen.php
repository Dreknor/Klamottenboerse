<?php

namespace App\Domain\Schichten;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Links;
use App\Enums\EinteilungStatus;
use App\Models\Einteilung;
use App\Models\Person;
use App\Models\Schicht;
use DomainException;

class HelferEintragen
{
    public function __invoke(Schicht $schicht, Person $person, string $quelle = 'online', bool $mailSenden = true, bool $ueberbuchenErlaubt = true): Einteilung
    {
        $vorhanden = $schicht->einteilungen()->where('person_id', $person->id)->first();
        if ($vorhanden?->status === EinteilungStatus::Zugesagt) {
            throw new DomainException("{$person->name} ist in dieser Schicht schon eingetragen.");
        }

        if (! $ueberbuchenErlaubt && $schicht->freiePlaetze() === 0) {
            throw new DomainException('Diese Schicht ist leider schon voll.');
        }

        $einteilung = $vorhanden ?? new Einteilung(['schicht_id' => $schicht->id, 'person_id' => $person->id]);
        $einteilung->fill(['status' => EinteilungStatus::Zugesagt, 'quelle' => $quelle])->save();

        if ($mailSenden) {
            Postausgang::einplanen($person, 'helfer_eingetragen', $schicht->boerse, [
                'schicht' => $schicht->bereich.', '.$schicht->beginn->locale('de')->isoFormat('dddd, D. MMMM, H:mm').'–'.$schicht->ende->format('H:i').' Uhr',
                'helfer_absage_link' => Links::helferAbsage($einteilung),
            ]);
        }

        return $einteilung;
    }
}
