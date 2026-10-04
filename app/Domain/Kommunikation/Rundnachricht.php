<?php

namespace App\Domain\Kommunikation;

use App\Enums\Zielgruppe;
use App\Models\Boerse;
use App\Models\Person;
use App\Models\PushAbo;
use Illuminate\Support\Collection;

/** Nachricht an einzelne Personen und/oder ganze Gruppen – per E-Mail und optional als Push. */
class Rundnachricht
{
    /**
     * Gruppen, die ohne aktuelle Börse funktionieren, stehen immer zur Wahl;
     * die übrigen beziehen sich auf die gewählte Börse.
     */
    public const OHNE_BOERSE = [Zielgruppe::Team, Zielgruppe::Interessenten, Zielgruppe::InteressentenKinderhaus];

    /**
     * @param  list<string>  $gruppen  Zielgruppe-Werte
     * @param  list<int>  $personIds
     * @return Collection<int, Person>
     */
    public function empfaenger(array $gruppen, array $personIds, ?Boerse $boerse): Collection
    {
        $mailplan = new MailplanAusfuehren;
        $personen = collect();

        foreach ($gruppen as $wert) {
            $gruppe = Zielgruppe::tryFrom($wert);
            if (! $gruppe || (! $boerse && ! in_array($gruppe, self::OHNE_BOERSE, true))) {
                continue;
            }
            // Interessenten-Gruppen brauchen eine Börse für „noch nicht angemeldet“ – ohne Börse: alle mit Einwilligung
            $personen = $personen->merge($boerse
                ? $mailplan->empfaenger($gruppe, $boerse)
                : $this->ohneBoerse($gruppe));
        }

        if ($personIds) {
            $personen = $personen->merge(Person::query()->whereIn('id', $personIds)->get());
        }

        return $personen->unique('id')->sortBy(['nachname', 'vorname'])->values();
    }

    /** @return array{gesamt: int, mail: int, push: int, namen: list<string>} */
    public function vorschau(Collection $personen): array
    {
        $mitPush = PushAbo::query()->whereIn('person_id', $personen->pluck('id'))->distinct()->pluck('person_id');

        return [
            'gesamt' => $personen->count(),
            'mail' => $personen->filter(fn (Person $p) => filled($p->email) && ! $p->loeschung_angefragt_at)->count(),
            'push' => $mitPush->count(),
            'namen' => $personen->take(40)->map(fn (Person $p) => $p->name)->all(),
        ];
    }

    /** @return array{mail: int, push: int} */
    public function senden(Collection $personen, string $betreff, string $text, ?Boerse $boerse, bool $mail, bool $push): array
    {
        $zaehler = ['mail' => 0, 'push' => 0];
        foreach ($personen as $person) {
            $ergebnis = Postausgang::freiEinplanen($person, $betreff, $text, $boerse, $mail, $push);
            $zaehler['mail'] += (int) $ergebnis['mail'];
            $zaehler['push'] += (int) $ergebnis['push'];
        }

        return $zaehler;
    }

    /** @return Collection<int, Person> */
    private function ohneBoerse(Zielgruppe $gruppe): Collection
    {
        $query = Person::query()->whereNotNull('email')->whereNull('loeschung_angefragt_at');

        return match ($gruppe) {
            Zielgruppe::Team => $query->whereHas('roles')->get(),
            Zielgruppe::Interessenten => $query->whereNotNull('info_mails_erlaubt_at')->get(),
            Zielgruppe::InteressentenKinderhaus => $query->whereNotNull('info_mails_erlaubt_at')->where('kinderhaus_bezug', '!=', 'keiner')->get(),
            default => collect(),
        };
    }
}
