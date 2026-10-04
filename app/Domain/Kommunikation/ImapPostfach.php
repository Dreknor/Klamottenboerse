<?php

namespace App\Domain\Kommunikation;

use App\Models\Person;
use App\Models\Posteingang;
use Illuminate\Support\Str;
use RuntimeException;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Message;

/**
 * Zugriff auf das Postfach (z. B. anmeldung@) per IMAP.
 * Neue Mails werden in die Tabelle "posteingang" übernommen und anhand der
 * Absenderadresse automatisch der passenden Person zugeordnet.
 */
class ImapPostfach
{
    private ?Client $client = null;

    public static function istKonfiguriert(): bool
    {
        return filled(config('imap.host')) && filled(config('imap.username'));
    }

    /** Ruft neue Mails ab. Gibt die Anzahl neu übernommener Mails zurück. */
    public function abrufen(string $ordner = 'INBOX'): int
    {
        $folder = $this->client()->getFolderByPath($ordner);
        if (! $folder) {
            throw new RuntimeException("Ordner {$ordner} nicht gefunden.");
        }

        $letzteUid = (int) Posteingang::query()->where('ordner', $ordner)->max('uid');

        $nachrichten = $folder->query()
            ->whereUid(($letzteUid + 1).':*')
            ->leaveUnread()
            ->setFetchOrderAsc()
            ->get();

        $neu = 0;
        foreach ($nachrichten as $message) {
            if ((int) $message->getUid() <= $letzteUid) {
                continue; // "N:*" liefert immer mindestens die letzte Mail
            }
            $this->uebernehmen($message, $ordner);
            $neu++;
        }

        return $neu;
    }

    /** Verschiebt die Mail auf dem Server (z. B. in den Spam- oder Papierkorb-Ordner). */
    public function verschieben(Posteingang $mail, string $zielordner): void
    {
        $folder = $this->client()->getFolderByPath($mail->ordner);
        $message = $folder?->query()->getMessageByUid($mail->uid);
        $message?->move($zielordner, true);
    }

    private function uebernehmen(Message $message, string $ordner): Posteingang
    {
        $absender = $message->getFrom()->first();
        $email = Str::lower((string) ($absender->mail ?? ''));

        $text = $message->hasTextBody()
            ? $message->getTextBody()
            : trim(html_entity_decode(strip_tags($message->getHTMLBody())));

        $anhaenge = [];
        foreach ($message->getAttachments() as $anhang) {
            $anhaenge[] = ['name' => $anhang->getName(), 'groesse' => $anhang->getSize()];
        }

        return Posteingang::create([
            'ordner' => $ordner,
            'uid' => (int) $message->getUid(),
            'message_id' => (string) $message->getMessageId(),
            'von_email' => $email,
            'von_name' => $absender->personal ?? null,
            'betreff' => Str::limit((string) $message->getSubject(), 250),
            'text' => $text,
            'anhaenge' => $anhaenge ?: null,
            'empfangen_at' => $message->getDate()->toDate() ?? now(),
            'person_id' => Person::query()->where('email', $email)->value('id'),
        ]);
    }

    private function client(): Client
    {
        if ($this->client) {
            return $this->client;
        }

        if (! self::istKonfiguriert()) {
            throw new RuntimeException('Das IMAP-Postfach ist nicht eingerichtet (IMAP_HOST, IMAP_USERNAME in der .env).');
        }

        $this->client = (new ClientManager)->make([
            'host' => config('imap.host'),
            'port' => config('imap.port'),
            'encryption' => config('imap.encryption'),
            'validate_cert' => config('imap.validate_cert'),
            'username' => config('imap.username'),
            'password' => config('imap.password'),
            'protocol' => 'imap',
        ]);
        $this->client->connect();

        return $this->client;
    }
}
