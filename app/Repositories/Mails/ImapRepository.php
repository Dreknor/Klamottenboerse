<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 29.08.2018
 * Time: 21:06
 */

namespace App\Repositories\Mails;

use App\Model\Interessenten;
use Carbon\Carbon;
use function Sodium\add;
use Webklex\IMAP\Facades\Client;
use Webklex\IMAP\Support\MessageCollection;

class ImapRepository
{
    public function connect()
    {
        $Client = Client::account('default');
        $Client->getConnection();

        return $Client;
    }

    public function unseenMessages()
    {
        try {
            $Client = $this->connect();

            $aFolder = $Client->getFolder('INBOX');
            $aMessage = $aFolder
                ->getUnseenMessages();

            return $aMessage;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findUid($uid, $email = '', $date = '')
    {
        $Client = $this->connect();
        $ordner = $Client->getFolder('INBOX');
        $Nachricht = [];
        $Nachricht = $ordner->getMessage($uid, 1, 1, true);

        if (isset($Nachricht) and $Nachricht != null) {
            return $Nachricht;
        } else {
            $ordner = $Client->getFolders(0, 'INBOX');

            foreach ($ordner as $Ordner) {
                $message = $Ordner->search()->from($email)->on($date)->setFetchAttachment(false)->get();
                if (isset($message) and count($message) > 0) {
                    foreach ($message as $Message) {
                        if ($Message->uid == $uid) {
                            return $Message;
                            break;
                        }
                    }
                }
            }

            $ordner = $Client->getFolders(0, 'Sent');
            foreach ($ordner as $Ordner) {
                $message = $Ordner->search()->from($email)->on($date)->setFetchAttachment(false)->get();
                if (isset($message) and count($message) > 0) {
                    foreach ($message as $Message) {
                        if ($Message->uid == $uid) {
                            return $Message;
                            break;
                        }
                    }
                }
            }
        }

        return $Nachricht;
    }

    public function deleteMessage($uid)
    {
        $Client = $this->connect();
        $ordner = $Client->getFolder('INBOX');
        $message = $ordner->getMessage($uid);

        return $message->moveToFolder('Trash');
    }

    public function spamMessage($uid)
    {
        $Client = $this->connect();
        $ordner = $Client->getFolder('INBOX');
        $message = $ordner->getMessage($uid);

        return $message->moveToFolder('0 Spamfilter.als Spam lernen');
    }

    public function findMailsOfEMail($email)
    {
        $Client = $this->connect();
        $messages = new MessageCollection();
        $ordner = $Client->getFolders(0, 'INBOX');

        foreach ($ordner as $Ordner) {
            $message = ($Ordner->search()->from($email)->setFetchAttachment(false)->get());
            if (count($message) > 0) {
                $messages = $messages->merge($message);
            }
        }

        $ordner = $Client->getFolders(0, 'Sent');

        foreach ($ordner as $Ordner) {
            $message = ($Ordner->search()->to($email)->setFetchAttachment(false)->get());
            if (count($message) > 0) {
                $messages = $messages->merge($message);
            }
        }

        $sorted = $messages->sortByDesc('date');

        return $sorted;
    }

    public function mailsInboxLastDays($Tage = 5)
    {
        $Interessenten = Interessenten::all();

        $Client = $this->connect();
        $aFolder = $Client->getFolder('INBOX');
        $aMessage = $aFolder->query()
            ->since(now()->subDays($Tage))
            ->setFetchBody(true)
            ->setFetchAttachment(false)
            ->get();

        $sorted = $aMessage->sortByDesc('date');
        $Mails = new MessageCollection();

        foreach ($sorted as $key => $Mail) {
            $Interressent = $Interessenten->where('mail', '=', $Mail->from[0]->mail)->first();
            if (isset($Interressent)) {
                $sorted[$key]->interessent = $Interressent;
            }
        }

        return $sorted;
    }
}
