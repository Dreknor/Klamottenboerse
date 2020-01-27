<?php

namespace App\Mail;

use App\Model\Interessenten;
use App\Model\VKnummer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Verkaeuferinfos extends Mailable
{
    use Queueable, SerializesModels;

    public $vknummer;
    public $Klamottenboerse;
    public $Interessent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(VKnummer $VKnummer, $attach)
    {
        $this->vknummer = $VKnummer;
        $this->Klamottenboerse=$VKnummer->Klamottenboerse;
        $this->attach = $attach;
        $this->Interessent=$VKnummer->vergeben_an_Interessent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
        ->text('mails.text.verkaeuferinfos')
        ->view('mails.verkaeuferinfos')
        ->attach($this->attach);
    }
}
