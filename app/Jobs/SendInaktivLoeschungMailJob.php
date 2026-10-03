<?php

namespace App\Jobs;

use App\Mail\InaktivLoeschungMail;
use App\Model\MailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class SendInaktivLoeschungMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $tries = 100;
    public $backoff = 300;

    public function __construct(public int $mailLogId, public string $vorname, public string $nachname)
    {
    }

    public function middleware(): array
    {
        return [new RateLimited('mails')];
    }

    public function handle(): void
    {
        $log = MailLog::findOrFail($this->mailLogId);
        if ($log->status === MailLog::STATUS_SENT) {
            return;
        }
        try {
            Mail::to($log->email)->send(new InaktivLoeschungMail($this->vorname, $this->nachname));
        } catch (TransportExceptionInterface $exception) {
            $log->update(['status' => MailLog::STATUS_FAILED, 'fehler' => $exception->getMessage()]);
            throw $exception;
        }
        $log->update(['status' => MailLog::STATUS_SENT, 'versendet_at' => now(), 'fehler' => null]);
    }

    public function failed(Throwable $exception): void
    {
        MailLog::whereKey($this->mailLogId)->update([
            'status' => MailLog::STATUS_FAILED,
            'fehler' => $exception->getMessage(),
        ]);
    }
}
