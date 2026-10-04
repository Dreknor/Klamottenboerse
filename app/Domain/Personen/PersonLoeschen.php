<?php

namespace App\Domain\Personen;

use App\Enums\TeilnahmeStatus;
use App\Models\Feedback;
use App\Models\Nachricht;
use App\Models\Notiz;
use App\Models\Person;
use App\Models\Posteingang;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

/**
 * Löscht eine Person datenschutzgerecht: Kontaktdaten, Mails, Notizen und Schichten verschwinden,
 * Verkaufs- und Abrechnungsdaten bleiben ohne Personenbezug für Statistik und Kassenbuch erhalten.
 */
class PersonLoeschen
{
    /** Hat die Person noch eine laufende Teilnahme oder eine offene Auszahlung? */
    public static function hatOffeneVorgaenge(Person $person): bool
    {
        return $person->teilnahmen()
            ->whereIn('status', [TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Angeboten, TeilnahmeStatus::Angeliefert, TeilnahmeStatus::Abgerechnet])
            ->whereHas('boerse', fn ($q) => $q->where('verkaufstag', '>=', today()->subDays(30)))
            ->exists();
    }

    public function __invoke(Person $person, string $grund): void
    {
        DB::transaction(function () use ($person, $grund) {
            $email = $person->email;

            $person->teilnahmen()->update(['person_id' => null]);
            $person->einteilungen()->delete();
            $person->reservierungen()->delete();
            Notiz::query()->whereMorphedTo('notizbar', $person)->delete();
            Feedback::query()->where('person_id', $person->id)->update(['person_id' => null]);
            Nachricht::query()->where('person_id', $person->id)->orWhere('email', $email ?? '-')->delete();
            Posteingang::query()->where('person_id', $person->id)->orWhere('von_email', $email ?? '-')->delete();
            Activity::query()->where('subject_type', $person->getMorphClass())->where('subject_id', $person->id)->delete();
            DB::table('password_reset_tokens')->where('email', $email ?? '-')->delete();
            $person->roles()->detach();

            $person->forceDelete();

            activity()->withProperties(['grund' => $grund])->log('Person gelöscht');
        });
    }
}
