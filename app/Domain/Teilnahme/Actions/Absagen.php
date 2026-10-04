<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Enums\TeilnahmeStatus;
use App\Models\Nummernreservierung;
use App\Models\Teilnahme;
use DomainException;

/**
 * Selbst-Absage oder Absage durch das Orga-Team: Die Nummer wird frei und die
 * Warteliste rückt automatisch nach.
 */
class Absagen
{
    public function __construct(private readonly WartelisteNachruecken $nachruecken) {}

    public function __invoke(Teilnahme $teilnahme, string $durch = 'verkaeufer', bool $mailSenden = true): void
    {
        if ($teilnahme->ist_kinderhaus) {
            throw new DomainException('Die Kinderhaus-Nummer kann nicht abgesagt werden.');
        }

        if (in_array($teilnahme->status, [TeilnahmeStatus::Angeliefert, TeilnahmeStatus::Abgerechnet, TeilnahmeStatus::Ausgezahlt], true)) {
            throw new DomainException('Die Ware ist bereits angeliefert – eine Absage ist nicht mehr möglich.');
        }

        if ($teilnahme->status === TeilnahmeStatus::Abgesagt) {
            return;
        }

        $alteNummer = $teilnahme->nummer;

        $teilnahme->update([
            'status' => TeilnahmeStatus::Abgesagt,
            'nummer' => null,
            'abgesagt_at' => now(),
            'angebot_bis' => null,
            'wartelisten_position' => null,
        ]);

        activity()->performedOn($teilnahme)
            ->withProperties(['nummer' => $alteNummer, 'durch' => $durch])
            ->log('Teilnahme abgesagt');

        if ($mailSenden && $teilnahme->person) {
            Postausgang::einplanen($teilnahme->person, 'absage_bestaetigt', $teilnahme->boerse, ['nummer' => (string) $alteNummer]);
        }

        // Reservierte Nummer wird für diese Börse frei – sonst bliebe der Platz blockiert.
        if ($teilnahme->person_id) {
            Nummernreservierung::query()->gueltigFuer($teilnahme->boerse)
                ->where('person_id', $teilnahme->person_id)->get()
                ->each(fn (Nummernreservierung $r) => $r->freigebenFuer($teilnahme->boerse, 'Absage'));
        }

        ($this->nachruecken)($teilnahme->boerse);
    }
}
