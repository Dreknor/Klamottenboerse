<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 29.08.2018
 * Time: 21:06
 */

namespace App\Repositories\Mails;


use function Sodium\add;
use Webklex\IMAP\Facades\Client;
use Webklex\IMAP\Support\MessageCollection;

class ImapRepository
{



    public function connect(){


            $Client = Client::account('default');
            $Client->getConnection();
            return $Client;
    }

    public function unseenMessages(){

        try {
            $Client = $this->connect();

            $aFolder = $Client->getFolder('INBOX');
            $aMessage = $aFolder->getUnseenMessages();
            return $aMessage;
        } catch (\Exception $e) {
            return NULL;
        }

    }

    public function findMailsOfEMail($email){

        $Client = $this->connect();
        $messages =new MessageCollection();
        $ordner = $Client->getFolders(0, 'INBOX');

        foreach ($ordner AS $Ordner){
           $message=( $Ordner->search()->from($email)->setFetchAttachment(false)->get());
           if (count($message)>0){
               $messages=$messages->merge($message);
           }
        }

        $ordner = $Client->getFolders(0, 'Sent');

        foreach ($ordner AS $Ordner){
            $message=( $Ordner->search()->to($email)->setFetchAttachment(false)->get());
            if (count($message)>0){
                $messages=$messages->merge($message);
            }
        }

        $sorted = $messages->sortByDesc('date');


        return $sorted;

    }




}