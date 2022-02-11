<?php

namespace App\Mail;

use App\Model\Interessenten;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class sendPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $name;
    protected $password;
    protected $anrede;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Interessenten $interessenten, $password)
    {
        $this->name = $interessenten->vorname;
        $this->password = $password;

        switch ($interessenten->anrede){
            case 'Frau':
                $this->anrede = 'Liebe ';
                break;
            case 'Herr':
                $this->anrede = 'Lieber ';
                break;
            default:
                $this->anrede = 'Liebe/r ';
                break;
        }

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Zugang Klamottenbörsenverwaltung')->view('mails.sendPassword')->with([
            'anrede' => $this->anrede,
            'name' => $this->name,
            'password' => $this->password,
        ]);
    }
}
