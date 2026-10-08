<?php

namespace App\Domain\Kommunikation;

use App\Enums\NachrichtStatus;
use App\Models\Nachricht;
use App\Models\Person;

/**
 * Testmail aus dem Backend (System) oder per „php artisan mail:test“.
 * Läuft über den normalen Versandweg und steht danach im Postausgang.
 */
class Testmail
{
    /** @return array{ok: bool, text: string} */
    public static function senden(string $email): array
    {
        $weg = self::versandweg();

        // „log“ oder „array“ verschicken gar nichts – das ist der häufigste Grund für „es kommt nichts an“
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            return ['ok' => false, 'text' => 'Es werden keine echten Mails verschickt: In der Datei .env steht MAIL_MAILER='.config('mail.default')
                .' (Mails landen nur in der Log-Datei). Für echten Versand MAIL_MAILER=smtp setzen und die Zugangsdaten eintragen.'];
        }

        $nachricht = Nachricht::create([
            'person_id' => Person::query()->where('email', $email)->value('id'),
            'typ' => 'testmail',
            'email' => $email,
            'betreff' => 'Testmail der Klamottenbörse',
            'inhalt' => "Hallo,\n\ndiese Mail wurde zum Testen des Mailversands verschickt ({$weg}, ".now()->format('d.m.Y H:i')." Uhr).\n\n"
                .'Wenn du sie liest, funktioniert der Versand. Du musst nichts weiter tun.',
            'status' => NachrichtStatus::Wartend,
        ]);

        if (! Postausgang::senden($nachricht)) {
            return ['ok' => false, 'text' => 'Testmail an '.$email.' ist fehlgeschlagen ('.$weg.'): '.$nachricht->fehler];
        }

        return ['ok' => true, 'text' => 'Der Mailserver hat die Testmail an '.$email.' angenommen ('.$weg.'). '
            .'Kommt sie in ein paar Minuten nicht an, bitte auch im Spam-Ordner nachsehen – und prüfen, ob die Absenderadresse zum Mail-Konto gehört.'];
    }

    /** Kurzbeschreibung des Versandwegs (ohne Passwort), damit man Tippfehler in der .env erkennt. */
    public static function versandweg(): string
    {
        $mailer = (string) config('mail.default');
        $absender = (string) config('mail.from.address');

        if ($mailer !== 'smtp') {
            return "Versandart {$mailer}, Absender {$absender}";
        }

        $smtp = config('mail.mailers.smtp');
        $schema = $smtp['scheme'] ?? null;

        return 'über '.$smtp['host'].':'.$smtp['port'].($schema ? " ({$schema})" : '')
            .', Benutzer '.($smtp['username'] ?: '–').', Absender '.$absender;
    }
}
