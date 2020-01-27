<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Mail\AnmeldungMoeglichMail;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\Mailvorlagen;
use App\Repositories\Mails\ImapRepository;
use App\Repositories\Mails\MailRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function __construct(ImapRepository $imapRepository, MailRepository $mailRepository)
    {
        $this->imapRepository = $imapRepository;
        $this->mailRepository = $mailRepository;
    }

    public function getInteressentenMail(Interessenten $id){


        if ($id->mail){
            $email = $id->mail;
            $Mails= $this->imapRepository->findMailsOfEMail($email);
        } else {
            $Mails  = array();
        }

        return response()->json(["Nachrichten"  => $Mails], 200);
    }

    public function getUidMail($uid, Request $request){

        $mail = $request->from;
        $date = $request->date;

        $Mails= $this->imapRepository->findUid($uid, $mail, $date);

        if (isset($Mails) and is_a($Mails, 'Illuminate\Database\Eloquent\Collection') and count($Mails) > 1){
            foreach ($Mails AS $Mail){
                if (is_object($Mail) ){
                    $Nachricht = $Mail;
                }
            }
        } elseif (isset($Mails)) {
            $Nachricht = $Mails;
        } else {
            $Nachricht = NULL;
        }

        if ($Nachricht == NULL){
            return response()->json(["Nachricht"  => "Keine Nachricht"], 400);
        }
        return response()->json(["Nachricht"  => $Nachricht], 200);
    }

    public function getMails(){
        $Mails = $this->imapRepository->mailsInboxLastDays(10);

        return response()->json(["Nachricht"  => $Mails], 200);
    }

    public function deleteMessage($uid){
        $status = $this->imapRepository->deleteMessage($uid);

        if ($status == true){
            return response()->json(["Nachricht"  => $status], 200);
        } else {
            return response()->json(["Nachricht"  => $status], 400);

        }

    }

    public function markSpamMail($uid){
        $status = $this->imapRepository->spamMessage($uid);

        if ($status == true){
            return response()->json(["Nachricht"  => $status], 200);
        } else {
            return response()->json(["Nachricht"  => $status], 400);

        }

    }

    public function unreadCount(){
        return response()->json(["Nachrichten"  => $this->imapRepository->unseenMessages()], 200);
    }

    public function composeNewMail(Interessenten $interessenten, Mailvorlagen $mailvorlagen = null){

        if (!$mailvorlagen) {
            $mailvorlagen=new Mailvorlagen();
        }

        return view('mails.composeMail',[
            "Interessent"   => $interessenten,
            "Vorlage"       => $this->mailRepository->replaceInMailvorlage($mailvorlagen, $interessenten)
        ]);

    }

    public function replyMail($uid){

        $Mail = $this->imapRepository->findUid($uid);

        return view('mails.replyMail',[
            "Mail"       => $Mail
        ]);

    }

    public function sendReply(MailRequest $request){

        Mail::to($request->input('email'))->send(new \App\Mail\Mail($request));

        return redirect(url('/home'))->with([
            'success'=> "Nachricht versandt."
        ]);
    }
    public function sendMail(Interessenten $interessent, MailRequest $request){

        Mail::to($interessent->mail)->send(new \App\Mail\Mail($request, $interessent));

        return redirect(url('interessent/'.$interessent->id))->with([
            'success'=> "Nachricht versandt."
        ]);
    }

    public function anmeldungMoeglich(){

        $Mailvorlage = Mailvorlagen::where('name', 'AnmeldungMoeglich')->first();
        $Klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        $interessenten = Interessenten::where('mail', '<>', '')->doesntHave('vknummern_vergeben')->get();
        $interessenten = $interessenten->unique('mail');
        $interessenten->load('vknummern_vergeben');


        foreach ($interessenten as $Interessent){



            $text = $this->mailRepository->replaceInMailvorlage(clone ($Mailvorlage), $Interessent, $Klamottenboerse)->text;
            Mail::to($Interessent->mail)
                ->queue(new AnmeldungMoeglichMail($Interessent, $Mailvorlage->betreff, $text));
        }



        return view('welcome');
    }
}
