<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use PDOException;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use Throwable;

/**
 * Übersetzt technische Fehler in Sätze, mit denen auch Laien etwas anfangen können:
 * was ist passiert und was kann man tun. Die technischen Details landen im Fehlerprotokoll.
 */
class Fehlermeldung
{
    public const ALLGEMEIN = 'Da ist leider etwas schiefgegangen. Bitte versuche es gleich noch einmal. Wenn es wieder passiert, sag bitte dem Orga-Team Bescheid – der Fehler wurde automatisch protokolliert.';

    /** Verständliche Meldung, oder null, wenn der Fehler keiner bekannten Art zugeordnet werden kann. */
    public static function bekannt(Throwable $e): ?string
    {
        for ($f = $e; $f; $f = $f->getPrevious()) {
            if ($meldung = self::einzeln($f)) {
                return $meldung;
            }
        }

        return null;
    }

    public static function fuer(Throwable $e): string
    {
        return self::bekannt($e) ?? self::ALLGEMEIN;
    }

    /**
     * Für Fehlerseiten (403/404): die Meldung nur zeigen, wenn wir sie selbst formuliert haben
     * (z. B. abort(404, 'Es gibt keine aktuelle Börse.')) – nicht die englischen Standardtexte von Laravel und Paketen.
     */
    public static function eigene(Throwable $e): ?string
    {
        $text = trim($e->getMessage());
        $standard = ['No query results', 'The route', 'This action is unauthorized', 'User does not have', 'Not Found', 'Forbidden', 'Unauthorized'];

        return $text === '' || str_starts_with($text, 'Invalid signature') || collect($standard)->contains(fn ($s) => str_starts_with($text, $s))
            ? null
            : $text;
    }

    /** Für den Postausgang: verständlich vorne, technischer Text in Klammern für die Fehlersuche. */
    public static function mitDetails(Throwable $e): string
    {
        return self::fuer($e).' (Technisch: '.mb_strimwidth(trim($e->getMessage()), 0, 500, '…').')';
    }

    private static function einzeln(Throwable $e): ?string
    {
        $text = mb_strtolower($e->getMessage());

        if ($e instanceof RfcComplianceException) {
            return 'Die E-Mail-Adresse ist ungültig. Bitte die Adresse prüfen und korrigieren.';
        }

        if ($e instanceof TransportExceptionInterface || str_contains($text, 'smtp') || str_contains($text, 'mailer')) {
            return self::mail($text);
        }

        if ($e instanceof ConnectionException) {
            return 'Ein anderer Server ist gerade nicht erreichbar. Bitte später noch einmal versuchen.';
        }

        if ($e instanceof RequestException) {
            return 'Ein anderer Server hat die Anfrage abgelehnt ('.$e->response->status().'). Bitte Zugangsdaten und Adresse prüfen.';
        }

        if (str_contains($text, 'imap')) {
            return 'Das Postfach (IMAP) ist nicht erreichbar oder hat die Anmeldung abgelehnt. Bitte die IMAP-Zugangsdaten in der Datei .env auf dem Server prüfen.';
        }

        if ($e instanceof QueryException || $e instanceof PDOException) {
            return self::datenbank($text);
        }

        if ($e instanceof FileCannotBeAdded) {
            return 'Die Datei konnte nicht gespeichert werden. Bitte prüfen, ob sie zu groß ist oder einen ungewöhnlichen Dateityp hat.';
        }

        if (str_contains($text, 'allowed memory size') || str_contains($text, 'maximum execution time')) {
            return 'Das war für den Server gerade zu viel Arbeit (zu große Liste oder Datei). Bitte die Auswahl verkleinern und noch einmal versuchen.';
        }

        if (str_contains($text, 'permission denied') || str_contains($text, 'failed to open stream') || str_contains($text, 'unable to create')) {
            return 'Der Server darf eine Datei nicht schreiben. Bitte die Schreibrechte für die Ordner storage/ und bootstrap/cache/ prüfen lassen.';
        }

        if (str_contains($text, 'disk quota') || str_contains($text, 'no space left')) {
            return 'Der Speicherplatz auf dem Server ist voll. Bitte alte Dateien löschen oder den Speicher erweitern lassen.';
        }

        return null;
    }

    private static function mail(string $text): string
    {
        $tipp = ' Die Zugangsdaten für den Mailversand stehen in der Datei .env auf dem Server (MAIL_HOST, MAIL_PORT, MAIL_SCHEME, MAIL_USERNAME, MAIL_PASSWORD).';

        return match (true) {
            str_contains($text, 'authenticat') || str_contains($text, '535') || str_contains($text, 'credentials') => 'Der Mailserver hat die Anmeldung abgelehnt – Benutzername oder Passwort stimmen nicht.'.$tipp,
            str_contains($text, 'certificate') || str_contains($text, 'ssl operation') || str_contains($text, 'openssl')
                || str_contains($text, 'starttls') || str_contains($text, 'crypto') || str_contains($text, 'wrong version number') => 'Die Verschlüsselung zum Mailserver passt nicht. Meist gehören Port 465 und MAIL_SCHEME=smtps bzw. Port 587 und MAIL_SCHEME=smtp zusammen.'.$tipp,
            str_contains($text, 'scheme') => 'Die Mail-Einstellung MAIL_SCHEME ist ungültig – erlaubt sind „smtp“ oder „smtps“.'.$tipp,
            str_contains($text, 'could not be established') || str_contains($text, 'timed out') || str_contains($text, 'getaddrinfo') || str_contains($text, 'connection refused') => 'Der Mailserver ist nicht erreichbar. Bitte Server-Adresse und Port prüfen – oder es später noch einmal versuchen.'.$tipp,
            str_contains($text, '550') || str_contains($text, '553') || str_contains($text, '554') || str_contains($text, 'rejected') || str_contains($text, 'relay') => 'Der Mailserver hat die Mail abgelehnt. Häufige Gründe: die Empfängeradresse gibt es nicht, oder die Absenderadresse (MAIL_FROM_ADDRESS) gehört nicht zum Mail-Konto.',
            str_contains($text, '421') || str_contains($text, '450') || str_contains($text, '451') || str_contains($text, '452') || str_contains($text, 'too many') => 'Der Mailserver ist gerade überlastet oder bremst (zu viele Mails). Bitte später erneut senden – ggf. das Stundenlimit in den Einstellungen senken.',
            default => 'Die Mail konnte nicht verschickt werden.'.$tipp,
        };
    }

    private static function datenbank(string $text): string
    {
        return match (true) {
            str_contains($text, 'access denied') || str_contains($text, 'connection refused') || str_contains($text, 'gone away') || str_contains($text, 'no such host') || str_contains($text, '2002') => 'Die Datenbank ist gerade nicht erreichbar. Bitte in ein paar Minuten noch einmal versuchen. Hält das an, bitte die Datenbank-Zugangsdaten in der .env prüfen lassen.',
            str_contains($text, 'duplicate') || str_contains($text, 'unique') => 'Diesen Eintrag gibt es schon (z. B. dieselbe E-Mail-Adresse oder Nummer). Bitte die Eingabe prüfen.',
            str_contains($text, 'foreign key') || str_contains($text, 'constraint') => 'Das geht nicht, weil noch andere Daten daran hängen (z. B. Teilnahmen, Bons oder Mails). Bitte diese zuerst entfernen.',
            str_contains($text, 'data too long') || str_contains($text, 'out of range') => 'Eine Eingabe ist zu lang oder die Zahl zu groß. Bitte kürzen und noch einmal speichern.',
            str_contains($text, "doesn't exist") || str_contains($text, 'unknown column') || str_contains($text, 'no such table') => 'Die Datenbank ist nicht auf dem neuesten Stand. Bitte unter System das Update ausführen (oder „php artisan migrate“).',
            default => 'Beim Speichern in der Datenbank ist ein Fehler aufgetreten. Bitte noch einmal versuchen.',
        };
    }
}
