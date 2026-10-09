<?php

namespace App\Domain\Kommunikation;

use App\Domain\Teilnahme\Links;
use App\Enums\BoerseStatus;
use App\Enums\EinteilungStatus;
use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use App\Enums\Zielgruppe;
use App\Models\Boerse;
use App\Models\Feedback;
use App\Models\MailplanEintrag;
use App\Models\Nachricht;
use App\Models\Person;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Plant alle fälligen Mailplan-Einträge ein. Jede Person erhält eine Vorlage pro Börse
 * höchstens einmal – auch wenn der Befehl mehrfach läuft.
 */
class MailplanAusfuehren
{
    /** @return int Anzahl eingeplanter Mails */
    public function __invoke(): int
    {
        $anzahl = 0;

        MailplanEintrag::query()
            ->where('aktiv', true)
            ->whereNull('eingeplant_at')
            ->whereHas('boerse', fn ($q) => $q->where('status', '!=', BoerseStatus::Abgeschlossen->value))
            ->with(['boerse.ort', 'vorlage'])
            ->get()
            ->filter(fn (MailplanEintrag $eintrag) => $eintrag->faelligAb()?->isPast())
            ->each(function (MailplanEintrag $eintrag) use (&$anzahl) {
                $anzahl += $this->eintragAusfuehren($eintrag);
            });

        return $anzahl;
    }

    public function eintragAusfuehren(MailplanEintrag $eintrag): int
    {
        $boerse = $eintrag->boerse;
        $schluessel = $eintrag->vorlage->schluessel;

        $bereitsErhalten = Nachricht::query()
            ->where('boerse_id', $boerse->id)
            ->where('typ', $schluessel)
            ->pluck('person_id')
            ->filter()
            ->flip();

        $anzahl = 0;
        foreach ($this->empfaenger($eintrag->zielgruppe, $boerse) as $person) {
            if ($bereitsErhalten->has($person->id)) {
                continue;
            }

            if (Postausgang::einplanen($person, $schluessel, $boerse, $this->zusatzdaten($schluessel, $boerse, $person), $eintrag->id)) {
                $anzahl++;
            }
        }

        $eintrag->update(['eingeplant_at' => now()]);
        activity()->performedOn($eintrag)->withProperties(['anzahl' => $anzahl])->log('Mailplan ausgeführt');

        return $anzahl;
    }

    /** @return Collection<int, Person> */
    public function empfaenger(Zielgruppe $zielgruppe, Boerse $boerse): Collection
    {
        $mitMail = fn (Builder $q) => $q->whereNotNull('email')->whereNull('loeschung_angefragt_at');

        $ohneAnmeldung = fn (Builder $q) => $q->whereDoesntHave('teilnahmen', fn ($t) => $t
            ->where('boerse_id', $boerse->id)
            ->where('status', '!=', TeilnahmeStatus::Abgesagt->value));

        $verkaeufer = fn () => Person::query()->tap($mitMail)->whereHas('teilnahmen', fn ($t) => $t
            ->where('boerse_id', $boerse->id)
            ->whereIn('status', [TeilnahmeStatus::Zugeteilt->value, TeilnahmeStatus::Angeliefert->value,
                TeilnahmeStatus::Abgerechnet->value, TeilnahmeStatus::Ausgezahlt->value]))->get();

        $helfer = fn () => Person::query()->tap($mitMail)->whereHas('einteilungen', fn ($e) => $e
            ->where('status', EinteilungStatus::Zugesagt->value)
            ->whereHas('schicht', fn ($s) => $s->where('boerse_id', $boerse->id)))->get();

        return match ($zielgruppe) {
            Zielgruppe::InteressentenKinderhaus => Person::query()->tap($mitMail)->tap($ohneAnmeldung)
                ->whereNotNull('info_mails_erlaubt_at')
                ->where('kinderhaus_bezug', '!=', KinderhausBezug::Keiner->value)->get(),
            Zielgruppe::Interessenten => Person::query()->tap($mitMail)->tap($ohneAnmeldung)
                ->whereNotNull('info_mails_erlaubt_at')->get(),
            Zielgruppe::Verkaeufer => $verkaeufer(),
            Zielgruppe::Warteliste => Person::query()->tap($mitMail)->whereHas('teilnahmen', fn ($t) => $t
                ->where('boerse_id', $boerse->id)->where('status', TeilnahmeStatus::Warteliste->value))->get(),
            Zielgruppe::Helfer => $helfer(),
            Zielgruppe::VerkaeuferUndHelfer => $verkaeufer()->merge($helfer())->unique('id')->values(),
            Zielgruppe::Team => Person::query()->tap($mitMail)->whereHas('roles')->get(),
        };
    }

    /** @return array<string, string> */
    private function zusatzdaten(string $schluessel, Boerse $boerse, Person $person): array
    {
        $daten = [];

        $teilnahme = $boerse->teilnahmen()->where('person_id', $person->id)->first();
        if ($teilnahme && $teilnahme->hatNummer()) {
            $daten['absage_link'] = Links::absage($teilnahme);
        }

        if ($schluessel === 'erinnerung_helfer') {
            $daten['schichten'] = $person->einteilungen()
                ->where('status', EinteilungStatus::Zugesagt->value)
                ->whereHas('schicht', fn ($s) => $s->where('boerse_id', $boerse->id))
                ->with('schicht')->get()
                ->sortBy('schicht.beginn')
                ->map(fn ($e) => '- '.$e->schicht->bereich.', '.$e->schicht->beginn->locale('de')->isoFormat('dddd, D. MMMM, H:mm')
                    .'–'.$e->schicht->ende->format('H:i').' Uhr ([absagen]('.Links::helferAbsage($e).'))')
                ->implode("\n");
        }

        if ($schluessel === 'feedback') {
            $feedback = Feedback::firstOrCreate(
                ['boerse_id' => $boerse->id, 'person_id' => $person->id, 'rolle' => $teilnahme ? 'verkaeufer' : 'helfer'],
                ['token' => Str::random(40)],
            );
            $daten['feedback_link'] = route('feedback.show', $feedback->token);
        }

        return $daten;
    }
}
