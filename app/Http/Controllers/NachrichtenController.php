<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 27.03.2016
 * Time: 23:11
 */

namespace App\Http\Controllers;


use App\Models\Interessenten\Interessenten;
use App\Models\Interessenten\Nachrichten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Interessenten\InteressentenRepository;




class NachrichtenController extends Controller
{
    public function __construct(InteressentenRepository $interessentenRepository)
    {
        $this->middleware('auth');
        $this->interessentenRepository = $interessentenRepository;

    }
    /*
     * Versendet Nachrichten und speichert diese in der DB
     */
    public function send($InteressentenID, Request $request, $return="true"){

        $Interessent=Interessenten::query()->findOrFail($InteressentenID);

        if ($Interessent->mail !=''){
            $this->senden($request, $Interessent);
            $this->store($InteressentenID, $request->betreff, $request->nachricht, $request->anhang);
        }

       if ($return='true'){
           return redirect(url('/Interessent'.'/'.$InteressentenID));
       }
    }

    /*
     * speichert Nachrichten
     */
    
    public function store($id, $betreff, $text, $anhang='') {
        Nachrichten::create([
            'interessent_id' => $id,
            'betreff' => $betreff,
            'nachricht' => $text,
            'pfad'      => $anhang
        ]);
    }

    public function senden($request, $Interessent){
        $subject = $request->betreff;
        $empfaenger=$Interessent->mail;
        $name=$Interessent->vorname.' '.$Interessent->nachname;
        $anhang=$request->anhang;

        Mail::send(array('text' => 'emails.email'), [
                'text'      => $request->nachricht,
                'id'        => $Interessent->id,
                'token'    =>  $Interessent->mail

            ], function($message) use ($subject, $empfaenger, $name, $anhang) {
            // note: if you don't set this, it will use the defaults from config/mail.php

            $message->from('anmeldung@klamottenboerse.de', 'Klamottenbörse');
            $message->to($empfaenger, $name)
                ->subject($subject);

            if ($anhang!=""){ $message->attach(storage_path('app\anhaenge\\'.$anhang));}
        });
    }

    /*
     * sendet E-Mails an eine ganze Gruppe
     * 
     */

    public function mailGruppe (Request $request, $gruppe="All") {

        switch ($gruppe){
            case "All":
                $Interssenten= $this->interessentenRepository->all();
                    foreach ($Interssenten AS $Interessent) {
                        if ($Interessent->mail !=''){
                            $this->store($Interessent->id, $request->betreff, $request->nachricht);
                            $this->senden($request, $Interessent);
                        }
                    }
                return redirect(url("/Ueberblick/$gruppe"));
                exit;

            case "Kinderhaus":
                $Interssenten= $this->interessentenRepository->Kinderhaus();
                foreach ($Interssenten AS $Interessent) {
                    if ($Interessent->mail !=''){
                        $this->store($Interessent->id, $request->betreff, $request->nachricht);
                        $this->senden($request, $Interessent);
                    }
                }
                return redirect(url("/Ueberblick/$gruppe"));
                exit;


            case "Mitarbeiter":
                $Interssenten= $this->interessentenRepository->Mitarbeiter();
                    foreach ($Interssenten AS $Interessent) {
                        if ($Interessent->mail !=''){
                            $this->store($Interessent->id, $request->betreff, $request->nachricht);
                            $this->senden($request, $Interessent);
                        }
                    }
                return redirect(url("/Ueberblick/$gruppe"));
                exit;
        }


    }
}