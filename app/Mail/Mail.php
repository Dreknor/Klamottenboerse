<?php

namespace App\Mail;

use App\Http\Requests\MailRequest;
use App\Model\Interessenten;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Mail extends Mailable
{
    use Queueable, SerializesModels;

    public $betreff;
    public $text;
    public $interessent;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MailRequest $request, Interessenten $interessent = null)
    {
        if (isset($interessent)){
            $this->interessent = $interessent;
        }

        $this->betreff = $request->input('betreff');
        $this->text= $request->input('text');
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
            ->cc(env('MAIL_FROM_ADDRESS'))
            ->subject($this->betreff)
            ->text('mails.text.mail')
            ->view('mails.mail')
            ;

    }
}
