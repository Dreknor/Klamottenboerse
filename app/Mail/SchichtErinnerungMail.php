<?php

namespace App\Mail;

use App\Model\Appointment;
use App\Model\Helfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchichtErinnerungMail extends Mailable
{
    use Queueable, SerializesModels;

    public Helfer $helfer;

    public Appointment $appointment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Helfer $helfer, Appointment $appointment)
    {
        $this->helfer = $helfer;
        $this->appointment = $appointment;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Erinnerung: Deine Helferschicht steht bevor')
            ->view('mails.schicht-erinnerung');
    }
}
