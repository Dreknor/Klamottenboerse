<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Links;
use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Teilnahme;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Das Orga-Team entscheidet über eine Nummern-Anfrage (Verkäufer mit schlechter Reputation). */
class AnfrageEntscheiden
{
    public function vergeben(Teilnahme $teilnahme, bool $mailSenden = true): Teilnahme
    {
        $teilnahme = DB::transaction(function () use ($teilnahme) {
            Boerse::query()->whereKey($teilnahme->boerse_id)->lockForUpdate()->first();
            $teilnahme->refresh();
            $this->pruefen($teilnahme);

            $vergabe = new Nummernvergabe($teilnahme->boerse);
            $nummer = $vergabe->hatPlatzFuer($teilnahme->person) ? $vergabe->naechsteNummer($teilnahme->person) : null;
            if ($nummer === null) {
                throw new DomainException('Gerade ist keine Nummer frei. Erst wenn jemand absagt, kann die Anfrage angenommen werden.');
            }

            $teilnahme->update(['nummer' => $nummer, 'status' => TeilnahmeStatus::Zugeteilt, 'zugeteilt_at' => now()]);

            return $teilnahme;
        });

        activity()->performedOn($teilnahme)->withProperties(['nummer' => $teilnahme->nummer])->log('Anfrage angenommen – Nummer vergeben');
        if ($mailSenden && $teilnahme->person) {
            Postausgang::einplanen($teilnahme->person, 'nummer_zugeteilt', $teilnahme->boerse, ['absage_link' => Links::absage($teilnahme)]);
        }

        return $teilnahme;
    }

    public function ablehnen(Teilnahme $teilnahme, bool $mailSenden = true): void
    {
        $this->pruefen($teilnahme);
        $teilnahme->update(['status' => TeilnahmeStatus::Abgesagt, 'abgesagt_at' => now()]);

        activity()->performedOn($teilnahme)->log('Anfrage abgelehnt');
        if ($mailSenden && $teilnahme->person) {
            Postausgang::einplanen($teilnahme->person, 'anfrage_abgelehnt', $teilnahme->boerse);
        }
    }

    private function pruefen(Teilnahme $teilnahme): void
    {
        if ($teilnahme->status !== TeilnahmeStatus::Angefragt) {
            throw new DomainException('Über diese Anmeldung wurde bereits entschieden.');
        }
    }
}
