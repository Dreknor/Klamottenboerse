<?php

namespace App\Domain\Personen;

use App\Domain\Kommunikation\Postausgang;
use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Löschkonzept: Wer 24 Monate an keiner Börse teilgenommen (und nicht geholfen) hat, wird angeschrieben.
 * Meldet sich die Person innerhalb von 4 Wochen nicht (Klick auf den Link im Portal), wird sie gelöscht.
 * Team-Mitglieder (mit Rolle) sind ausgenommen. Gewünschte Löschungen werden nach der Börse erledigt.
 */
class InaktiveBereinigen
{
    public const MONATE = 24;

    public const FRIST_TAGE = 28;

    public function __construct(private readonly PersonLoeschen $loeschen) {}

    /** @return array{angeschrieben:int, geloescht:int} */
    public function __invoke(): array
    {
        $angeschrieben = 0;
        $geloescht = 0;

        foreach ($this->inaktive()->whereNull('inaktiv_angeschrieben_at')->get() as $person) {
            if ($person->email) {
                Postausgang::einplanen($person, 'inaktiv_loeschung', null, ['frist' => now()->addDays(self::FRIST_TAGE)->isoFormat('D. MMMM YYYY')]);
            }
            $person->forceFill(['inaktiv_angeschrieben_at' => now()])->save();
            $angeschrieben++;
        }

        // Nach Ablauf der Frist ohne Lebenszeichen löschen
        $this->inaktive()
            ->where('inaktiv_angeschrieben_at', '<', now()->subDays(self::FRIST_TAGE))
            ->where(fn ($q) => $q->whereNull('letzte_aktivitaet_at')->orWhereColumn('letzte_aktivitaet_at', '<', 'inaktiv_angeschrieben_at'))
            ->get()
            ->each(function (Person $person) use (&$geloescht) {
                ($this->loeschen)($person, 'inaktiv seit '.self::MONATE.' Monaten');
                $geloescht++;
            });

        // Selbst beantragte Löschungen, die wegen einer laufenden Börse warten mussten
        Person::query()->whereNotNull('loeschung_angefragt_at')->get()
            ->reject(fn (Person $p) => PersonLoeschen::hatOffeneVorgaenge($p))
            ->each(function (Person $person) use (&$geloescht) {
                ($this->loeschen)($person, 'auf eigenen Wunsch');
                $geloescht++;
            });

        return compact('angeschrieben', 'geloescht');
    }

    /** Personen ohne Teilnahme, Schicht oder Anmeldung in den letzten 24 Monaten. */
    public function inaktive(): Builder
    {
        $stichtag = now()->subMonths(self::MONATE);

        return Person::query()
            ->whereDoesntHave('roles')
            ->whereNull('loeschung_angefragt_at')
            ->where('created_at', '<', $stichtag)
            ->where(fn ($q) => $q->whereNull('letzte_aktivitaet_at')->orWhere('letzte_aktivitaet_at', '<', $stichtag)->orWhereNotNull('inaktiv_angeschrieben_at'))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('teilnahmen')
                ->join('boersen', 'boersen.id', '=', 'teilnahmen.boerse_id')
                ->whereColumn('teilnahmen.person_id', 'personen.id')
                ->where(fn ($w) => $w->where('boersen.verkaufstag', '>=', $stichtag)->orWhere('teilnahmen.angemeldet_at', '>=', $stichtag)))
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('einteilungen')
                ->join('schichten', 'schichten.id', '=', 'einteilungen.schicht_id')
                ->whereColumn('einteilungen.person_id', 'personen.id')
                ->where('schichten.beginn', '>=', $stichtag));
    }
}
