<?php

namespace App\Jobs;

use App\Mail\ErinnerungVerkaeuferMail;
use App\Model\MailLog;
use App\Model\Mailvorlagen;
use App\Repositories\Mails\MailRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Versendet eine einzelne Verkäufer-Erinnerungs-Mail und protokolliert das
 * Ergebnis in mail_logs. Nutzt denselben "mails"-Rate-Limiter wie die
 * "Anmeldung möglich"-Mails, damit beide Massen-Mail-Arten sich das
 * stündliche Hoster-Limit teilen, statt es unabhängig voneinander zu
 * überschreiten (der eigentliche Flaschenhals der alten Implementierung).
 */
class SendErinnerungVerkaeuferMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(public int $mailLogId)
    {
    }

    public function middleware(): array
    {
        return [new RateLimited('mails')];
    }

    public function handle(MailRepository $mailRepository): void
    {
        $mailLog = MailLog::with(['interessent', 'klamottenboerse'])->find($this->mailLogId);

        if (! $mailLog || $mailLog->status === MailLog::STATUS_SENT) {
            return;
        }

        if (! $mailLog->interessent || ! $mailLog->klamottenboerse) {
            $mailLog->update([
                'status' => MailLog::STATUS_FAILED,
                'fehler' => 'Interessent oder Klamottenbörse nicht mehr vorhanden.',
            ]);

            return;
        }

        $mailvorlage = Mailvorlagen::where('name', $mailLog->typ)->first();

        if (! $mailvorlage) {
            $mailLog->update([
                'status' => MailLog::STATUS_FAILED,
                'fehler' => "Mailvorlage \"{$mailLog->typ}\" nicht gefunden.",
            ]);

            return;
        }

        $vorlage = $mailRepository->replaceInMailvorlage(clone $mailvorlage, $mailLog->interessent, $mailLog->klamottenboerse);

        try {
            Mail::to($mailLog->email)->send(
                new ErinnerungVerkaeuferMail($mailLog->interessent, $vorlage->betreff, $vorlage->text, $vorlage->html)
            );

            $mailLog->update([
                'status' => MailLog::STATUS_SENT,
                'betreff' => $vorlage->betreff,
                'versendet_at' => now(),
                'fehler' => null,
            ]);
        } catch (Throwable $e) {
            $mailLog->update([
                'status' => MailLog::STATUS_FAILED,
                'betreff' => $vorlage->betreff,
                'fehler' => $e->getMessage(),
            ]);

            Log::error('Versand der ErinnerungVerkaeufer-Mail fehlgeschlagen', [
                'mail_log_id' => $mailLog->id,
                'email' => $mailLog->email,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        MailLog::where('id', $this->mailLogId)->update([
            'status' => MailLog::STATUS_FAILED,
            'fehler' => $exception->getMessage(),
        ]);
    }
}
