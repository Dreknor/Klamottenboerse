<?php

namespace App\Domain\Kommunikation;

use App\Domain\Push\Push;
use App\Enums\NachrichtStatus;
use App\Models\Boerse;
use App\Models\Mailvorlage;
use App\Models\Nachricht;
use App\Models\Person;
use App\Support\Demo;
use App\Support\Einstellungen;
use App\Support\Fehlermeldung;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Alle ausgehenden Mails laufen hierüber: Vorlage wählen, Platzhalter füllen,
 * als Nachricht speichern. Der Versand erfolgt gedrosselt durch versendeFaellige().
 */
class Postausgang
{
    /**
     * Plant eine Mail aus einer Vorlage ein. Gibt null zurück, wenn die Person keine E-Mail hat
     * oder die Vorlage fehlt.
     *
     * @param  array<string, string|int|null>  $daten  zusätzliche Platzhalter
     */
    public static function einplanen(
        Person $person,
        string $vorlage,
        ?Boerse $boerse = null,
        array $daten = [],
        ?int $mailplanEintragId = null,
    ): ?Nachricht {
        if (blank($person->email)) {
            return null;
        }

        $mailvorlage = Mailvorlage::query()->where('schluessel', $vorlage)->first();
        if (! $mailvorlage) {
            Log::warning("Mailvorlage {$vorlage} fehlt – Mail an Person {$person->id} nicht eingeplant.");

            return null;
        }

        $platzhalter = Platzhalter::fuer($person, $boerse, $daten);

        $nachricht = Nachricht::create([
            'person_id' => $person->id,
            'boerse_id' => $boerse?->id,
            'mailplan_eintrag_id' => $mailplanEintragId,
            'typ' => $vorlage,
            'email' => $person->email,
            'betreff' => Platzhalter::ersetzen($mailvorlage->betreff, $platzhalter),
            'inhalt' => Platzhalter::ersetzen($mailvorlage->inhalt, $platzhalter),
            'status' => NachrichtStatus::Wartend,
        ]);

        // Erinnerungen u. ä. zusätzlich aufs Handy – nur wer Push eingeschaltet hat
        if ($mailvorlage->push) {
            Push::einplanen($person, $nachricht->betreff, $nachricht->inhalt, self::pushZiel($vorlage, $platzhalter));
        }

        return $nachricht;
    }

    /**
     * Freie Nachricht, z. B. an Gruppen. Platzhalter wie {vorname} werden ersetzt.
     * Per E-Mail (wenn vorhanden) und auf Wunsch zusätzlich als Push.
     *
     * @return array{mail: bool, push: bool}
     */
    public static function freiEinplanen(Person $person, string $betreff, string $text, ?Boerse $boerse, bool $mail = true, bool $push = false): array
    {
        $platzhalter = Platzhalter::fuer($person, $boerse);
        $betreff = Platzhalter::ersetzen($betreff, $platzhalter);
        $text = Platzhalter::ersetzen($text, $platzhalter);

        $nachricht = $mail && filled($person->email) && ! $person->loeschung_angefragt_at
            ? Nachricht::create([
                'person_id' => $person->id, 'boerse_id' => $boerse?->id, 'typ' => 'rundnachricht', 'email' => $person->email,
                'betreff' => $betreff, 'inhalt' => $text, 'status' => NachrichtStatus::Wartend,
            ])
            : null;

        $gepusht = $push && Push::einplanen($person, $betreff, $text, self::pushZiel('rundnachricht', $platzhalter)) !== null;

        return ['mail' => $nachricht !== null, 'push' => $gepusht];
    }

    /**
     * Wohin führt ein Tipp auf die Push-Nachricht? Team-Erinnerungen ins Backend,
     * alles andere direkt (angemeldet) ins Portal.
     *
     * @param  array<string, string>  $platzhalter
     */
    private static function pushZiel(string $typ, array $platzhalter): string
    {
        if (str_starts_with($typ, 'aufgabe')) {
            return route('admin.aufgaben.index');
        }

        return $platzhalter['portal_link'] ?? url('/');
    }

    /** Wie viele Mails dürfen jetzt noch raus, ohne das Stundenlimit zu überschreiten? */
    public static function restkontingent(): int
    {
        $limit = (int) Einstellungen::get('mail_max_pro_stunde');
        $letzteStunde = Nachricht::query()
            ->where('status', NachrichtStatus::Versendet)
            ->where('versendet_at', '>=', now()->subHour())
            ->count();

        return max(0, $limit - $letzteStunde);
    }

    /** Versendet wartende Nachrichten bis zum Stundenlimit. Gibt die Anzahl versendeter Mails zurück. */
    public static function versendeFaellige(): int
    {
        $kontingent = self::restkontingent();
        if ($kontingent === 0) {
            return 0;
        }

        // Einzelmails (Nummer zugeteilt, Antworten …) vor Rundmails aus dem Mailplan.
        $nachrichten = Nachricht::query()
            ->where('status', NachrichtStatus::Wartend)
            ->orderByRaw('mailplan_eintrag_id is not null')
            ->orderBy('id')
            ->limit($kontingent)
            ->get();

        $versendet = 0;
        foreach ($nachrichten as $nachricht) {
            if (self::senden($nachricht)) {
                $versendet++;
            }
        }

        return $versendet;
    }

    public static function senden(Nachricht $nachricht): bool
    {
        if (Demo::aktiv() && Demo::istBeispieladresse($nachricht->email)) {
            $nachricht->update(['status' => NachrichtStatus::Versendet, 'fehler' => 'Demo: erfundene Beispielperson – nicht zugestellt']);

            return true;
        }

        try {
            Mail::to($nachricht->email)->send(new VorlagenMail($nachricht->betreff, $nachricht->inhalt));
            $nachricht->update(['status' => NachrichtStatus::Versendet, 'versendet_at' => now(), 'fehler' => null]);

            return true;
        } catch (Throwable $e) {
            $nachricht->update(['status' => NachrichtStatus::Fehler, 'fehler' => Fehlermeldung::mitDetails($e)]);
            report($e);

            return false;
        }
    }
}
