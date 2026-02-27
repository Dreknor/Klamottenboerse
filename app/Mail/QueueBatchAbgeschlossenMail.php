<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueBatchAbgeschlossenMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param int    $batchNummer    Nummer des abgeschlossenen Batches (1-basiert)
     * @param int    $batchAnzahl    Gesamtanzahl der Batches
     * @param int    $mailsInBatch   Anzahl der Mails in diesem Batch
     * @param int    $mailsGesamt    Gesamtanzahl aller einzuplanenden Mails
     * @param string $boerseName     Name / Datum der Klamottenbörse
     */
    public function __construct(
        public readonly int    $batchNummer,
        public readonly int    $batchAnzahl,
        public readonly int    $mailsInBatch,
        public readonly int    $mailsGesamt,
        public readonly string $boerseName,
    ) {}

    public function build(): static
    {
        $istLetzterBatch = $this->batchNummer === $this->batchAnzahl;

        $betreff = $istLetzterBatch
            ? "[Klamottenbörse] ✓ Alle {$this->mailsGesamt} Anmeldungs-Mails versandt"
            : "[Klamottenbörse] Batch {$this->batchNummer}/{$this->batchAnzahl} abgeschlossen ({$this->mailsInBatch} Mails)";

        return $this
            ->from(env('MAIL_FROM_ADDRESS'))
            ->subject($betreff)
            ->view('mails.queue-batch-abgeschlossen');
    }
}

