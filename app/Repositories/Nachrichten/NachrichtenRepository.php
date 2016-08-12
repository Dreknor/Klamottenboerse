<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 11.08.2016
 * Time: 17:45
 */

namespace App\Repositories\Nachrichten;


use App\Models\Interessenten\Nachrichten;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;

class NachrichtenRepository
{
    

    public function store($InteressentenID, $betreff, $text, $anhang='') {
        Nachrichten::create([
            'interessent_id' => $InteressentenID,
            'betreff' => $betreff,
            'nachricht' => $text,
            'pfad'      => $anhang
        ]);
    }

    public function senden($Nachricht, $Interessent){
        $subject = $Nachricht['betreff'];
        $empfaenger=$Interessent->mail;
        $name=$Interessent->vorname.' '.$Interessent->nachname;
        $anhang=$Nachricht['anhang'];

        Mail::send(array('text' => $Nachricht['view']), [
            'Interessent'        => $Interessent,
            'msg'           => $Nachricht['text']

        ], function($message) use ($subject, $empfaenger, $name, $anhang) {
            // note: if you don't set this, it will use the defaults from config/mail.php

            $message->from('anmeldung@klamottenboerse.de', 'Klamottenbörse');
            $message->to($empfaenger, $name)
                ->subject($subject);

            if ($anhang!=""){ $message->attach(storage_path('app\anhaenge\\'.$anhang));}
        });
    }


    public function send ($Interessent, $Nachricht) {
        
        if ($Interessent->mail != "") {

            $this->store($Interessent->id, $Nachricht['betreff'], $Nachricht['text'], $Nachricht['anhang']);
            $this->senden($Nachricht, $Interessent);
        }
        
    }
        

}