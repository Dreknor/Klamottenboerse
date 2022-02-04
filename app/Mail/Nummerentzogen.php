<?php

namespace App\Mail;

use App\Model\Interessenten;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Nummerentzogen extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Interessenten $interessenten)
    {
        $this->interessent = $interessenten;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->text('mails.text.removeNumber')
            ->view('mails.removeNumber')
            ->with([
                'Interessent' => $this->interessent,
            ]);
    }
}
