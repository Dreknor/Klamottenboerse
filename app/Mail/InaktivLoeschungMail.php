<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InaktivLoeschungMail extends Mailable
{
    public function __construct(public string $vorname, public string $nachname)
    {
    }

    public function build()
    {
        return $this->subject('Dein Eintrag bei der Klamottenbörse')
            ->replyTo(config('mail.from.address'), config('mail.from.name'))
            ->view('mails.inaktiv-loeschung')
            ->text('mails.text.inaktiv-loeschung');
    }
}
