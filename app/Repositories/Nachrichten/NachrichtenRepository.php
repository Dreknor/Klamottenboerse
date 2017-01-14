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
use App\Repositories\Klamottenboerse;


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
            $message->bcc('anmeldung@klamottenboerse.de', 'Klamottenbörse');
            $message->to($empfaenger, $name)
                ->subject($subject);

            if ($anhang!=""){ $message->attach(storage_path('app\anhaenge\\'.$anhang));}
        });
    }


    public function send ($Interessent, $Nachricht) {
        
        if ($Interessent->mail != "") {
            $Nachricht= $this->replaceString($Nachricht, $Interessent);

            $this->senden($Nachricht, $Interessent);
            $this->store($Interessent->id, $Nachricht['betreff'], $Nachricht['nachricht'], $Nachricht['anhang']);

        }
        
    }

    public function replaceString ($Nachricht, $Interessent ) {

            $Absender=Auth::user()->name;
            $KlamottenboersenRepo=new Klamottenboerse\KlamottenboersenRepository();
            $Klamottenboerse=$KlamottenboersenRepo->latest();


        if (isset($Interessent->vknummern_vergeben->vknummer)){
            $vknummer=$Interessent->vknummern_vergeben->vknummer;
        } else {
            $vknummer="Bisher keine Verkäufernummer vergeben.";
        }

        if ($Interessent->anrede == "Herr"){
            $Anrede = "Sehr geehrter Herr";
        } elseif ($Interessent->anrede == "Frau"){
            $Anrede = "Sehr geehrte Frau";
        } else {
            $Anrede = "Liebe Familie";
        }

        if ($Interessent->anrede == "Herr"){
            $Liebe = "Lieber";
        } elseif ($Interessent->anrede == "Frau"){
            $Liebe = "Liebe";
        } else {
            $Liebe = "Liebe Familie";
        }

        $SearchStrings =["VORNAME", "NACHNAME", "ANREDE","LIEBE", "ABSENDER", "EMAIL", "VKNUMMER", "DATUM", "ANMELDUNG"];
        $ReplaceStrings =[$Interessent->vorname, $Interessent->nachname, $Anrede, $Liebe, $Absender, $Interessent->mail, $vknummer, $Klamottenboerse->datum->format('d.m.Y') , $Klamottenboerse->anmeldung->format('d.m.Y')];

        $Nachricht=str_replace($SearchStrings, $ReplaceStrings, $Nachricht);

        return $Nachricht;
    }
        

}