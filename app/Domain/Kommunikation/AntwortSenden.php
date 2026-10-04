<?php

namespace App\Domain\Kommunikation;

use App\Enums\NachrichtStatus;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Posteingang;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/** Antwort auf eine Mail aus dem Posteingang – wird sofort versendet und protokolliert. */
class AntwortSenden
{
    public function __invoke(Posteingang $mail, string $text, Person $autor): Nachricht
    {
        $betreff = Str::startsWith((string) $mail->betreff, ['Re:', 'AW:', 'RE:']) ? $mail->betreff : 'Re: '.$mail->betreff;

        $zitat = collect(preg_split('/\R/', (string) $mail->text))
            ->take(40)
            ->map(fn ($zeile) => '> '.$zeile)
            ->implode("\n");

        $inhalt = $text."\n\n---\n\nAm ".$mail->empfangen_at->format('d.m.Y H:i').' schrieb '.($mail->von_name ?: $mail->von_email).":\n\n".$zitat;

        $nachricht = Nachricht::create([
            'person_id' => $mail->person_id,
            'typ' => 'antwort',
            'email' => $mail->von_email,
            'betreff' => $betreff,
            'inhalt' => $inhalt,
            'status' => NachrichtStatus::Wartend,
        ]);

        $mailable = new VorlagenMail($betreff, $inhalt);
        if ($mail->message_id) {
            $mailable->withSymfonyMessage(function ($message) use ($mail) {
                $message->getHeaders()->addIdHeader('In-Reply-To', trim($mail->message_id, '<>'));
                $message->getHeaders()->addIdHeader('References', trim($mail->message_id, '<>'));
            });
        }

        try {
            Mail::to($mail->von_email)->send($mailable);
            $nachricht->update(['status' => NachrichtStatus::Versendet, 'versendet_at' => now()]);
            $mail->update(['beantwortet_at' => now(), 'gelesen_at' => $mail->gelesen_at ?? now()]);
            activity()->performedOn($mail)->causedBy($autor)->log('Mail beantwortet');
        } catch (\Throwable $e) {
            $nachricht->update(['status' => NachrichtStatus::Fehler, 'fehler' => $e->getMessage()]);
            report($e);
        }

        return $nachricht;
    }
}
