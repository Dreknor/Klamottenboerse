<?php

namespace App\Domain\Teilnahme\Actions;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Reputation\Reputation;
use App\Domain\Teilnahme\Links;
use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Teilnahme;
use Illuminate\Support\Facades\DB;

/**
 * Bietet freie Plätze der Reihe nach der Warteliste an. Das Angebot gilt
 * "angebot_stunden" lang; danach verfällt es und der Nächste ist dran.
 */
class WartelisteNachruecken
{
    /** @return int Anzahl neu verschickter Angebote */
    public function __invoke(Boerse $boerse): int
    {
        $this->abgelaufeneAngeboteBeenden($boerse);

        if ($boerse->verkaufstag->isPast()) {
            return 0;
        }

        $angebote = 0;

        while (true) {
            $teilnahme = DB::transaction(function () use ($boerse) {
                Boerse::query()->whereKey($boerse->id)->lockForUpdate()->first();

                $naechste = $boerse->teilnahmen()
                    ->where('status', TeilnahmeStatus::Warteliste)
                    ->orderBy('wartelisten_position')
                    ->with('person')
                    ->first();

                // Schlechte Reputation: kein automatisches Angebot – wird zur Anfrage ans Orga-Team
                while ($naechste?->person && Reputation::automatischGesperrt($naechste->person)) {
                    $naechste->update(['status' => TeilnahmeStatus::Angefragt, 'wartelisten_position' => null]);
                    activity()->performedOn($naechste)->log('Nicht automatisch nachgerückt (Reputation) – Anfrage ans Orga-Team');

                    $naechste = $boerse->teilnahmen()
                        ->where('status', TeilnahmeStatus::Warteliste)
                        ->orderBy('wartelisten_position')
                        ->with('person')
                        ->first();
                }

                if (! $naechste) {
                    return null;
                }

                $vergabe = new Nummernvergabe($boerse);
                $nummer = $vergabe->hatPlatzFuer($naechste->person) ? $vergabe->naechsteNummer($naechste->person) : null;
                if ($nummer === null) {
                    return null;
                }

                $naechste->update([
                    'status' => TeilnahmeStatus::Angeboten,
                    'nummer' => $nummer,
                    'angebot_bis' => now()->addHours($boerse->angebot_stunden),
                ]);

                return $naechste;
            });

            if (! $teilnahme) {
                break;
            }

            $angebote++;
            activity()->performedOn($teilnahme)->withProperties(['nummer' => $teilnahme->nummer])->log('Nummer von Warteliste angeboten');

            if ($teilnahme->person) {
                Postausgang::einplanen($teilnahme->person, 'warteliste_angebot', $boerse, [
                    'angebot_link' => Links::angebot($teilnahme),
                    'angebot_bis' => $teilnahme->angebot_bis->locale('de')->isoFormat('dddd, D. MMMM, H:mm [Uhr]'),
                ]);
            }
        }

        return $angebote;
    }

    private function abgelaufeneAngeboteBeenden(Boerse $boerse): void
    {
        $boerse->teilnahmen()
            ->where('status', TeilnahmeStatus::Angeboten)
            ->where('angebot_bis', '<', now())
            ->with('person')
            ->get()
            ->each(function (Teilnahme $teilnahme) use ($boerse) {
                $teilnahme->update([
                    'status' => TeilnahmeStatus::Abgesagt,
                    'nummer' => null,
                    'abgesagt_at' => now(),
                    'wartelisten_position' => null,
                ]);
                activity()->performedOn($teilnahme)->log('Angebot abgelaufen');

                if ($teilnahme->person) {
                    Postausgang::einplanen($teilnahme->person, 'angebot_abgelaufen', $boerse);
                }
            });
    }
}
