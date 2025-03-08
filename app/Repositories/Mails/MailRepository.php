<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 25.09.2018
 * Time: 18:42
 */

namespace App\Repositories\Mails;

use App\Http\Requests\MailRequest;
use App\Mail\Verkaeuferinfos;
use App\Model\Interessenten;
use App\Model\Mailvorlagen;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class MailRepository
{
    public function sendVerkaeuferInfo(VKnummer $VKnummer)
    {
        $Mailvorlage = Mailvorlagen::query()->where('name', '=', 'VerkäuferInfos')->first();
        $Mailvorlage = $this->replaceInMailvorlage($Mailvorlage, $VKnummer->vergeben_an_Interessent);

        $Mailtext = new MailRequest($Mailvorlage->toArray());

        Mail::to($VKnummer->vergeben_an_Interessent->mail)->send(new \App\Mail\Mail($Mailtext, $VKnummer->vergeben_an_Interessent));

    }

    public function sendRuecknahmeNummer(Interessenten $interessenten)
    {
        $Mailvorlage = Mailvorlagen::query()->where('name', '=', 'RuecknahmeNummer')->first();
        $Mailvorlage = $this->replaceInMailvorlage($Mailvorlage, $interessenten);

        $Mailtext = new MailRequest($Mailvorlage->toArray());

        Mail::to($interessenten->mail)->send(new \App\Mail\Mail($Mailtext, $interessenten));
    }

    public function replaceInMailvorlage(Mailvorlagen $Vorlage, Interessenten $interessenten, $Klamottenboerse = null)
    {
        $mailvorlagen = $Vorlage;

        if (is_null($Klamottenboerse)) {
            $KlamottenboersenRepository = new KlamottenboersenRepository();
            $Klamottenboerse = $KlamottenboersenRepository->aktuelleKlamottenboerse();
        }

        //Anrede
        if ($interessenten->anrede == 'Herr') {
            $Anrede = 'Sehr geehrter Herr';
            $Liebe = 'Lieber';
        } elseif ($interessenten->anrede == 'Frau') {
            $Anrede = 'Sehr geehrte Frau';
            $Liebe = 'Liebe';
        } elseif ($interessenten->anrede == 'Familie') {
            $Anrede = 'Sehr geehrte Familie';
            $Liebe = 'Liebe Familie';
        } else {
            $Anrede = '';
            $Liebe = '';
        }

        if (isset(auth()->user()->name)) {
            $absender = auth()->user()->name;
        } else {
            $absender = 'Das Team der Klamottenboerse';
        }

        $ReplaceStrings = [
            'VORNAME' => $interessenten->vorname ?? '',
            'NACHNAME'=> $interessenten->nachname ?? '',
            'ANREDE'=> $Anrede,
            'LIEBE'=> $Liebe,
            'ABSENDER' => $absender,
            'EMAIL'=> $interessenten->mail ?? '',
            'VKNUMMER'=> $interessenten->vknummern_vergeben->vknummer ?? '',
            'DATUM' => $Klamottenboerse->datum->format('d.m.Y'),
            'ANMELDUNG' => $Klamottenboerse->anmeldung->format('d.m.Y'),
            'ANNAHME' => $Klamottenboerse->datum->subDay()->format('d.m.Y'),
            'ORT' => $Klamottenboerse->ort,
            'ADRESSE' => $Klamottenboerse->adresse,
            'ANLIEFERUNG_AB'=> $Klamottenboerse->anlieferung_von,
            'ANLIEFERUNG_BIS' => $Klamottenboerse->anlieferung_bis,
            'ABHOLUNG_AB' => $Klamottenboerse->abholung_von,
            'ABHOLUNG_BIS' => $Klamottenboerse->abholung_bis,
            'MAXTEILE' => $Klamottenboerse->maxTeile,
        ];

        $Nachricht_text = str_replace(array_keys($ReplaceStrings), $ReplaceStrings, $mailvorlagen->text);
        $Nachricht_html = str_replace(array_keys($ReplaceStrings), $ReplaceStrings, $mailvorlagen->html);

        $mailvorlagen->betreff = str_replace(array_keys($ReplaceStrings), $ReplaceStrings, $mailvorlagen->betreff);
        $mailvorlagen->text = $Nachricht_text;
        $mailvorlagen->html = $Nachricht_html;

        return $mailvorlagen;
    }

    public function sendDeleteInteressent(Interessenten $interessenten)
    {
        $Mailvorlage = Mailvorlagen::query()->where('name', '=', 'InteressentLoeschen')->first();
        $Mailvorlage = $this->replaceInMailvorlage($Mailvorlage, $interessenten);

        $Mailtext = new MailRequest($Mailvorlage->toArray());

        Mail::to($interessenten->mail)->send(new \App\Mail\Mail($Mailtext, $interessenten));
    }
}
