<?php

namespace App\Domain\Kommunikation;

use App\Models\Person;
use App\Models\Posteingang;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Nur für die Entwicklung: Mailpit hat kein IMAP, aber eine REST-API.
 * Übernommen werden nur Mails, die an die Postfach-Adresse (IMAP_USERNAME) gerichtet sind –
 * die eigenen ausgehenden Mails landen in Mailpit ja ebenfalls.
 */
class MailpitPostfach
{
    public const ORDNER = 'mailpit';

    public function abrufen(): int
    {
        $basis = rtrim((string) config('imap.mailpit_url'), '/');
        $postfach = Str::lower((string) config('imap.username'));
        $bekannt = Posteingang::query()->where('ordner', self::ORDNER)->pluck('uid')->flip();

        $liste = Http::timeout(10)->get($basis.'/api/v1/messages', ['limit' => 200])->throw()->json('messages', []);

        $neu = 0;
        foreach (array_reverse($liste) as $eintrag) {
            $uid = (int) sprintf('%u', crc32($eintrag['ID']));
            $empfaenger = collect($eintrag['To'] ?? [])->pluck('Address')->map(fn ($a) => Str::lower($a));

            if ($bekannt->has($uid) || ! $empfaenger->contains($postfach)) {
                continue;
            }

            $mail = Http::timeout(10)->get($basis.'/api/v1/message/'.$eintrag['ID'])->throw()->json();
            $email = Str::lower($mail['From']['Address'] ?? '');

            Posteingang::create([
                'ordner' => self::ORDNER,
                'uid' => $uid,
                'message_id' => $mail['MessageID'] ?? null,
                'von_email' => $email,
                'von_name' => ($mail['From']['Name'] ?? '') ?: null,
                'betreff' => Str::limit((string) ($mail['Subject'] ?? ''), 250),
                'text' => ($mail['Text'] ?? '') ?: Mailinhalt::textAusHtml($mail['HTML'] ?? ''),
                'html' => ($mail['HTML'] ?? '') ?: null,
                'anhaenge' => collect($mail['Attachments'] ?? [])->map(fn ($a) => ['name' => $a['FileName'], 'groesse' => $a['Size']])->all() ?: null,
                'empfangen_at' => Carbon::parse($mail['Date'] ?? $eintrag['Created'])->setTimezone(config('app.timezone')),
                'person_id' => Person::query()->where('email', $email)->value('id'),
            ]);
            $neu++;
        }

        return $neu;
    }
}
