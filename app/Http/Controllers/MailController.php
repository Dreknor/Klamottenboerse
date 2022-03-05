<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Mail\AnmeldungMoeglichMail;
use App\Mail\ErinnerungVerkaeuferMail;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\Mailvorlagen;
use App\Repositories\Mails\ImapRepository;
use App\Repositories\Mails\MailRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function __construct(ImapRepository $imapRepository, MailRepository $mailRepository)
    {
        $this->imapRepository = $imapRepository;
        $this->mailRepository = $mailRepository;
    }

    public function getInteressentenMail(Interessenten $id)
    {
        if ($id->mail) {
            $email = $id->mail;
            $Mails = $this->imapRepository->findMailsOfEMail($email);
        } else {
            $Mails = [];
        }

        return response()->json(['Nachrichten'  => $Mails], 200);
    }

    public function getUidMail($uid, Request $request)
    {
        $mail = $request->from;
        $date = $request->date;

        $Mail = $this->imapRepository->findUid($uid, $mail, $date);
        if ($Mail == null) {
            return response()->json(['Nachricht'  => 'Keine Nachricht'], 400);
        }

        $Mail->setFlag('Seen');

        return response()->json(['Nachricht'  => $this->imapRepository->toArray($Mail)], 200);
    }

    public function getMails()
    {
        $Mails = $this->imapRepository->mailsInboxLastDays(10);

        $mails = [];
        foreach ($Mails as $key => $Mail) {
            $Interressent = Interessenten::query()->where('mail', '=', $Mail->from[0]->mail)->first();

            $EMail=[
                'from'       => $Mail->header->from[0]->mail,
                'subject'       => $Mail->header->subject[0],
                'bodies'       => $Mail->bodies,
                'flags'       =>$Mail->flags,
                'date'       => $Mail->header->date[0],
                'uid'       => $Mail->uid
            ];



            if (isset($Interressent)) {
                $mails[$key]['interessent'] = $Interressent;
                $mails[$key]['mail'] = $EMail;
            } else {
                $mails[$key]['mail'] = $EMail;
            }
        }

        return response()->json(['Nachricht'  => $mails], 200);
    }

    public function deleteMessage($uid)
    {
        $status = $this->imapRepository->deleteMessage($uid);

        if ($status == true) {
            return response()->json(['Nachricht'  => $status], 200);
        } else {
            return response()->json(['Nachricht'  => $status], 400);
        }
    }

    public function markSpamMail($uid)
    {
        $status = $this->imapRepository->spamMessage($uid);

        if ($status == true) {
            return response()->json(['Nachricht'  => $status], 200);
        } else {
            return response()->json(['Nachricht'  => $status], 400);
        }
    }

    public function unreadCount()
    {
        return response()->json(['Nachrichten'  => $this->imapRepository->unseenMessages()], 200);
    }

    public function composeNewMail(Interessenten $interessenten, Mailvorlagen $mailvorlagen = null)
    {
        if (! $mailvorlagen) {
            $mailvorlagen = new Mailvorlagen();
        }

        return view('mails.composeMail', [
            'Interessent'   => $interessenten,
            'Vorlage'       => $this->mailRepository->replaceInMailvorlage($mailvorlagen, $interessenten),
        ]);
    }

    public function replyMail($uid)
    {
        $Mail = $this->imapRepository->findUid($uid);

        return view('mails.replyMail', [
            'Mail'       => $Mail,
        ]);
    }

    public function sendReply(MailRequest $request)
    {
        Mail::to($request->email)->send(new \App\Mail\Mail($request));

        return redirect(url('/home'))->with([
            'success'=> 'Nachricht versandt.',
        ]);
    }

    public function sendMail(Interessenten $interessent, MailRequest $request)
    {
        Mail::to($interessent->mail)->send(new \App\Mail\Mail($request, $interessent));

        return redirect(url('interessent/'.$interessent->id))->with([
            'success'=> 'Nachricht versandt.',
        ]);
    }

    public function anmeldungMoeglich()
    {
        $Klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if ($Klamottenboerse->anmeldung->format('d.m.Y') == Carbon::now()->format('d.m.Y') and $Klamottenboerse->sendInvitation == 1) {
            $Mailvorlage = Mailvorlagen::where('name', 'AnmeldungMoeglich')->first();

            $interessenten = Interessenten::where('mail', '<>', '')->doesntHave('vknummern_vergeben')->get();
            $interessenten = $interessenten->unique('mail');
            $interessenten->load('vknummern_vergeben');

            foreach ($interessenten as $Interessent) {
                $vorlage = $this->mailRepository->replaceInMailvorlage(clone $Mailvorlage, $Interessent, $Klamottenboerse);

                Mail::to($Interessent->mail)
                    ->queue(new AnmeldungMoeglichMail($Interessent, $vorlage->betreff, $vorlage->text, $vorlage->html));
            }

            return view('welcome');
        } else {
            //dump($Klamottenboerse->anmeldung->format('d.m.Y'). "== ".Carbon::now()->format('d.m.Y'));
        }
    }

    public function erinnerungVerkaeufer()
    {
        $Klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if ($Klamottenboerse->sendErinnerung > 0 and $Klamottenboerse->datum->subDays($Klamottenboerse->sendErinnerung)->format('d.m.Y') == Carbon::now()->format('d.m.Y')) {
            $Mailvorlage = Mailvorlagen::where('name', 'erinnerungVerkaeufer')->first();
            $vknummern_vergeben = $Klamottenboerse->vknummern_vergeben;

            foreach ($vknummern_vergeben as $vknummer) {
                $Interessent= $vknummer->vergeben_an_Interessent;
                $vorlage = $this->mailRepository->replaceInMailvorlage(clone $Mailvorlage, $Interessent, $Klamottenboerse);

                Mail::to($Interessent->mail)->queue(new ErinnerungVerkaeuferMail($Interessent, $vorlage->betreff, $vorlage->text, $vorlage->html));
            }

        }
    }
}
