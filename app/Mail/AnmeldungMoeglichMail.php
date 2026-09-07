<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnmeldungMoeglichMail extends Mailable
{
    use Queueable, SerializesModels;

    public $interessent;

    public $betreff;

    public $text;

    public $html;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($interessenten, $betreff, $text, $html)
    {
        $this->interessent = $interessenten;
        $this->betreff = $betreff;
        $this->text = $text;
        $this->html = $html;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from(env('MAIL_FROM_ADDRESS'))
            ->subject($this->betreff)
            ->text('mails.text.mail')
            ->view('mails.mail');
    }
}
