<?php

namespace App\Domain\Schichten;

use App\Domain\Kommunikation\Postausgang;
use App\Enums\EinteilungStatus;
use App\Models\Einteilung;
use App\Models\Person;

/** Ein Helfer sagt seine Schicht ab – das Orga-Team erfährt es sofort, damit es Ersatz suchen kann. */
class HelferAbsagen
{
    public function __invoke(Einteilung $einteilung): void
    {
        if ($einteilung->status === EinteilungStatus::Abgesagt) {
            return; // doppelter Klick: niemanden zweimal benachrichtigen
        }

        $einteilung->update(['status' => EinteilungStatus::Abgesagt]);
        activity()->performedOn($einteilung)->log('Helfer hat Schicht abgesagt');

        $schicht = $einteilung->schicht;
        $daten = [
            'helfer' => $einteilung->person?->name ?? 'Ein Helfer',
            'schicht' => $schicht->bereich.', '.$schicht->beginn->locale('de')->isoFormat('dddd, D. MMMM, H:mm').'–'.$schicht->ende->format('H:i').' Uhr',
            'besetzung' => $schicht->zusagen()->count().' von '.$schicht->soll,
            'schichten_link' => route('admin.schichten.index'),
        ];

        Person::query()->role(['admin', 'orga'])
            ->whereNotNull('email')
            ->whereKeyNot($einteilung->person_id)
            ->get()
            ->each(fn (Person $person) => Postausgang::einplanen($person, 'helfer_abgesagt', $schicht->boerse, $daten));
    }
}
