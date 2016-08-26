<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 11.08.2016
 * Time: 17:45
 */

namespace App\Repositories\Nachrichten;


use App\Models\Interessenten\Nachrichten;
use Illuminate\Support\Facades\Auth;
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

        if (!isset($Nachricht['view']) OR $Nachricht['view'] == ""){
            $Nachricht['view'] = "emails.email";
        }

        Mail::send(array('text' => $Nachricht['view']), [
            'Interessent'        => $Interessent,
            'msg'           => $Nachricht['nachricht']

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
            $Nachricht= $this->replaceString($Nachricht, $Interessent);

            
            $this->store($Interessent->id, $Nachricht['betreff'], $Nachricht['nachricht'], $Nachricht['anhang']);
            $this->senden($Nachricht, $Interessent);
        }
        
    }

    public function replaceString ($Nachricht, $Interessent ) {

            $Absender=Auth::user()->name;

        if (isset($Interessent->vknummern_vergeben->vknummer)){
            $vknummer=$Interessent->vknummern_vergeben->vknummer;
        } else {
            $vknummer="Bisher keine Verkäufernummer vergeben.";
        }

        $SearchStrings =["VORNAME", "NACHNAME", "ANREDE", "ABSENDER", "EMAIL", "VKNUMMER"];
        $ReplaceStrings =[$Interessent->vorname, $Interessent->nachname, $Interessent->anrede, $Absender, $Interessent->mail, $vknummer];

        $Nachricht=str_replace($SearchStrings, $ReplaceStrings, $Nachricht);

        return $Nachricht;
    }
        

}