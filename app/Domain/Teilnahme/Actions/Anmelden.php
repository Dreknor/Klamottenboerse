<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Links;
use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Person;
use App\Models\Teilnahme;
use Illuminate\Support\Facades\DB;

/**
 * Meldet eine Person als Verkäufer an. Wer zuerst kommt, bekommt eine Nummer – egal, ob er
 * schon einmal verkauft hat. Ist kein Platz frei, kommt die Person auf die Warteliste.
 */
class Anmelden
{
    public function __invoke(Boerse $boerse, Person $person, string $quelle = 'online', bool $mailSenden = true): Teilnahme
    {
        [$teilnahme, $neu] = DB::transaction(function () use ($boerse, $person, $quelle) {
            // Sperre auf die Börse: gleichzeitige Anmeldungen werden nacheinander verarbeitet.
            Boerse::query()->whereKey($boerse->id)->lockForUpdate()->first();

            $vorhanden = Teilnahme::query()
                ->where('boerse_id', $boerse->id)
                ->where('person_id', $person->id)
                ->first();

            if ($vorhanden && $vorhanden->status !== TeilnahmeStatus::Abgesagt) {
                return [$vorhanden, false];
            }

            $teilnahme = $vorhanden ?? new Teilnahme(['boerse_id' => $boerse->id, 'person_id' => $person->id]);
            $teilnahme->fill([
                'quelle' => $quelle,
                'angemeldet_at' => now(),
                'abgesagt_at' => null,
                'angebot_bis' => null,
            ]);

            $vergabe = new Nummernvergabe($boerse);
            $nummer = $vergabe->hatPlatzFuer($person) ? $vergabe->naechsteNummer($person) : null;

            if ($nummer !== null) {
                $teilnahme->fill([
                    'nummer' => $nummer,
                    'status' => TeilnahmeStatus::Zugeteilt,
                    'zugeteilt_at' => now(),
                    'wartelisten_position' => null,
                ]);
            } else {
                $teilnahme->fill([
                    'nummer' => null,
                    'status' => TeilnahmeStatus::Warteliste,
                    'wartelisten_position' => (int) $boerse->teilnahmen()->max('wartelisten_position') + 1,
                ]);
            }

            $teilnahme->save();

            return [$teilnahme, true];
        });

        if (! $neu) {
            return $teilnahme;
        }

        $zugeteilt = $teilnahme->status === TeilnahmeStatus::Zugeteilt;

        if ($mailSenden) {
            Postausgang::einplanen($person, $zugeteilt ? 'nummer_zugeteilt' : 'warteliste', $boerse, [
                'absage_link' => Links::absage($teilnahme),
            ]);
        }

        activity()->performedOn($teilnahme)
            ->withProperties(['nummer' => $teilnahme->nummer, 'quelle' => $quelle])
            ->log($zugeteilt ? 'Nummer zugeteilt' : 'Auf Warteliste gesetzt');

        return $teilnahme;
    }
}
