<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QueueBatchAbgeschlossenMail extends Mailable
{
    use Queueable, SerializesModels;

    public int    $batchNummer;
    public int    $batchAnzahl;
    public int    $mailsInBatch;
    public int    $mailsGesamt;
    public string $boerseName;

    /**
     * @param int    $batchNummer    Nummer des abgeschlossenen Batches (1-basiert)
     * @param int    $batchAnzahl    Gesamtanzahl der Batches
     * @param int    $mailsInBatch   Anzahl der Mails in diesem Batch
     * @param int    $mailsGesamt    Gesamtanzahl aller einzuplanenden Mails
     * @param string $boerseName     Name / Datum der Klamottenbörse
     */
    public function __construct(int $batchNummer, int $batchAnzahl, int $mailsInBatch, int $mailsGesamt, string $boerseName)
    {
        $this->batchNummer  = $batchNummer;
        $this->batchAnzahl  = $batchAnzahl;
        $this->mailsInBatch = $mailsInBatch;
        $this->mailsGesamt  = $mailsGesamt;
        $this->boerseName   = $boerseName;
    }

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
