<?php

namespace App\Domain\Personen;

use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Person;
use App\Models\Teilnahme;
use Illuminate\Support\Collection;

/**
 * Schnelle Personensuche für Live-Suchfelder: Name, E-Mail, Telefon –
 * oder eine Verkäufernummer der aktuellen Börse.
 */
class PersonenSuche
{
    /** @return Collection<int, array<string, mixed>> */
    public function suchen(string $begriff, ?Boerse $boerse = null, int $grenze = 15): Collection
    {
        $begriff = trim($begriff);
        if ($begriff === '') {
            return collect();
        }

        $query = Person::query()->select(['id', 'vorname', 'nachname', 'email', 'telefon']);

        if ($boerse && ctype_digit($begriff) && strlen($begriff) === 3) {
            $query->whereIn('id', Teilnahme::query()->where('boerse_id', $boerse->id)->where('nummer', (int) $begriff)->select('person_id'));
        } else {
            foreach (preg_split('/\s+/', $begriff) as $wort) {
                $wort = str_replace(['%', '_'], ['\%', '\_'], $wort);
                $query->where(fn ($q) => $q->where('vorname', 'like', "%{$wort}%")->orWhere('nachname', 'like', "%{$wort}%")
                    ->orWhere('email', 'like', "%{$wort}%")->orWhere('telefon', 'like', "%{$wort}%"));
            }
        }

        $personen = $query->orderBy('nachname')->orderBy('vorname')->limit($grenze)->get();
        $nummern = $boerse
            ? Teilnahme::query()->where('boerse_id', $boerse->id)->where('status', '!=', TeilnahmeStatus::Abgesagt->value)->whereIn('person_id', $personen->pluck('id'))->pluck('nummer', 'person_id')
            : collect();

        return $personen->map(fn (Person $p) => [
            'id' => $p->id,
            'name' => $p->nachname.', '.$p->vorname,
            'email' => $p->email,
            'telefon' => $p->telefon,
            'angemeldet' => $nummern->has($p->id),
            'nummer' => $nummern->get($p->id),
            'url' => route('admin.personen.show', $p->id),
        ]);
    }
}
