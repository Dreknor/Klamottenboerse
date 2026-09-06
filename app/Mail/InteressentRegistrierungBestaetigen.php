<?php

namespace App\Mail;

use App\Model\Interessenten;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InteressentRegistrierungBestaetigen extends Mailable
{
    use Queueable, SerializesModels;

    public Interessenten $interessent;

    public string $verificationUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Interessenten $interessent, string $verificationUrl)
    {
        $this->interessent = $interessent;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Bitte bestätige deine Registrierung')
            ->view('mails.registrierung-bestaetigen');
    }
}
