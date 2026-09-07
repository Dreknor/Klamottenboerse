<?php

namespace App\Mail;

use App\Model\Interessenten;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InteressentLoeschungBestaetigen extends Mailable
{
    use Queueable, SerializesModels;

    public Interessenten $interessent;

    public string $confirmationUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Interessenten $interessent, string $confirmationUrl)
    {
        $this->interessent = $interessent;
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
            ->subject('Bestätige die Löschung deiner Registrierung')
            ->view('mails.loeschung-bestaetigen');
    }
}
