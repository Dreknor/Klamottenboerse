<?php

namespace App\Jobs;

use App\Mail\SchichtErinnerungMail;
use App\Model\MailLog;
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
 * Versendet eine einzelne Schicht-Erinnerungsmail an einen Helfer und
 * protokolliert das Ergebnis in mail_logs. Teilt sich den "mails"-
 * Rate-Limiter mit den übrigen Massen-Mail-Versänden.
 */
class SendSchichtErinnerungMailJob implements ShouldQueue
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

    public function handle(): void
    {
        $mailLog = MailLog::with(['helfer.appointment'])->find($this->mailLogId);

        if (! $mailLog || $mailLog->status === MailLog::STATUS_SENT) {
            return;
        }

        $helfer = $mailLog->helfer;
        $appointment = $helfer?->appointment;

        if (! $helfer || ! $appointment) {
            $mailLog->update([
                'status' => MailLog::STATUS_FAILED,
                'fehler' => 'Helfer oder Termin nicht mehr vorhanden.',
            ]);

            return;
        }

        try {
            Mail::to($mailLog->email)->send(new SchichtErinnerungMail($helfer, $appointment));

            $mailLog->update([
                'status' => MailLog::STATUS_SENT,
                'versendet_at' => now(),
                'fehler' => null,
            ]);
        } catch (Throwable $e) {
            $mailLog->update([
                'status' => MailLog::STATUS_FAILED,
                'fehler' => $e->getMessage(),
            ]);

            Log::error('Versand der Schicht-Erinnerungsmail fehlgeschlagen', [
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
