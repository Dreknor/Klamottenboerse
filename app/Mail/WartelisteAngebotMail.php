<?php

namespace App\Mail;

use App\Model\VKnummer;
use App\Model\Warteliste;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WartelisteAngebotMail extends Mailable
{
    use Queueable, SerializesModels;

    public Warteliste $warteliste;

    public VKnummer $vknummer;

    public string $confirmationUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Warteliste $warteliste, VKnummer $vknummer, string $confirmationUrl)
    {
        $this->warteliste = $warteliste;
        $this->vknummer = $vknummer;
        $this->confirmationUrl = $confirmationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Ein Verkäuferplatz ist für dich frei geworden!')
            ->view('mails.warteliste-angebot');
    }
}
