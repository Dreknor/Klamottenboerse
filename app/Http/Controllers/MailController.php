<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Repositories\Mails\ImapRepository;

class MailController extends Controller
{
    public function __construct(ImapRepository $imapRepository)
    {
        $this->imapRepository = $imapRepository;
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

    public function unreadCount(){
        return response()->json(["Nachrichten"  => $this->imapRepository->unseenMessages()->count()], 200);
    }
}
