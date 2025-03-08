<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 29.08.2018
 * Time: 21:06
 */

namespace App\Repositories\Mails;

use App\Model\Interessenten;
use Illuminate\Support\Facades\Log;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\Message;

class ImapRepository
{
    public function connect()
    {
        try {
            $Client = Client::account('default');
            $Client->getConnection();
            return $Client;
        } catch (\Exception $e) {

            Log::info($e->getMessage());
            abort( 500, 'Verbindung zum Mailserver konnte nicht hergestellt werden');
        }

    }

    public function unseenMessages()
    {
        try {
            $Client = $this->connect();

            $aFolder = $Client->getFolder('INBOX');
            $aMessage = $aFolder->search()->fetchOrderDesc()->unseen()->leaveUnread()->get();


            return $aMessage;
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            return $e;
        }
    }

    public function findUid($uid)
    {
        $Client = $this->connect();
        $ordner = $Client->getFolder('INBOX');
        $Nachricht = $ordner->messages()->where('uid',$uid)->get();

        if (!is_null($Nachricht) and count($Nachricht) > 0) {
            return $Nachricht->first();
        } else {
            $ordner = $Client->getFolders();
            foreach ($ordner as $Ordner) {
                $message = $Ordner->messages()->where('uid',$uid)->get();
                if (!is_null($message) and count($message) > 0) {
                    return $message->first();
                }
            }
        }
    }

    public function deleteMessage($uid)
    {

        $message = $this->findUid($uid);
        $message->move('Trash');
        return true;
    }

    public function spamMessage($uid)
    {

        $message = $this->findUid($uid);

        return $message->move('0 Spamfilter.als Spam lernen');
    }

    public function findMailsOfEMail($email , $folder = 'INBOX')
    {
        $Client = $this->connect();
        $ordner = $Client->getFolderByName($folder);


        if ($folder === 'INBOX'){
            $messages = $ordner->query()->setFetchOrder("DESC")->from($email)->get();
        } else {
            $messages = $ordner->query()->setFetchOrder("DESC")->to($email)->get();
        }

        $sorted = $messages->sortByDesc(function ($item) {
            return $item->getDate();
        });

        return $sorted;
    }

    public function mailsInboxLastDays($Tage = 5)
    {
        try {
            $Client = $this->connect();
            $aFolder = $Client->getFolder('INBOX');
            $aMessage = $aFolder->query()
                    ->since(now()->subDays($Tage))
                    ->setFetchBody(true)
                    ->get();

                $sorted = $aMessage->sortByDesc(function ($item) {
                    return $item->getDate();
                });

                return $sorted;

        } catch (\Exception $e) {
            return $e;
        }

    }

    public function toArray(Message $message){
        $nachricht = [
            'from' => [
                'full' => $message->getFrom()[0]->full,
                'personal' => $message->getFrom()[0]->personal,
                'mail' => $message->getFrom()[0]->mail
            ],
            'subject' => $message->getSubject()[0],
            'bodies' => [
                'text' => $message->getTextBody(),
                'html' => $message->getHTMLBody(),
            ],
        ];

        $interessent = $this->isInteressent($message->getFrom()[0]->mail);

        $nachricht['interessent'] = $interessent;

        return $nachricht;
    }

    public function isInteressent($mail){
        $Interessent = Interessenten::where('mail', $mail)->first();
        if (!is_null($Interessent)){
            return $Interessent;
        } else {
            return null;
        }
    }
}
