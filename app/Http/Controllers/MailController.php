<?php

namespace App\Http\Controllers;

use App\Http\Requests\MailRequest;
use App\Jobs\SendErinnerungVerkaeuferMailJob;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\MailLog;
use App\Model\Mailvorlagen;
use App\Repositories\Mails\ImapRepository;
use App\Repositories\Mails\MailRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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
        // Nutzt dasselbe Kommando wie der geplante Task, damit Versand,
        // Protokollierung (mail_logs) und Drosselung auf max. 55 Mails/Stunde
        // unabhängig vom Aufrufer (Cron oder dieser Route) identisch ablaufen.
        Artisan::call('mail:anmeldung-moeglich');

        return view('welcome');
    }

    public function erinnerungVerkaeufer()
    {
        $Klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if (! $Klamottenboerse) {
            return;
        }

        if ($Klamottenboerse->sendErinnerung > 0 and $Klamottenboerse->datum->subDays($Klamottenboerse->sendErinnerung)->format('d.m.Y') == Carbon::now()->format('d.m.Y')) {
            $typ = 'erinnerungVerkaeufer';

            if (! Mailvorlagen::where('name', $typ)->exists()) {
                Log::error("Mailvorlage \"{$typ}\" nicht gefunden. Verkäufer-Erinnerung nicht versendet.");

                return;
            }

            $vknummern_vergeben = $Klamottenboerse->vknummern_vergeben;

            // Bereits protokollierte Mails für diese Börse nicht erneut einplanen,
            // damit ein wiederholter Aufruf (z. B. Retry des Schedulers) keine
            // doppelten Mails erzeugt.
            $bereitsProtokolliert = MailLog::typ($typ)
                ->where('klamottenboerse_id', $Klamottenboerse->id)
                ->pluck('email')
                ->all();

            foreach ($vknummern_vergeben as $vknummer) {
                $Interessent = $vknummer->vergeben_an_Interessent;

                if (! $Interessent || ! $Interessent->mail || in_array($Interessent->mail, $bereitsProtokolliert, true)) {
                    continue;
                }

                $mailLog = MailLog::create([
                    'interessent_id' => $Interessent->id,
                    'klamottenboerse_id' => $Klamottenboerse->id,
                    'typ' => $typ,
                    'email' => $Interessent->mail,
                    'status' => MailLog::STATUS_QUEUED,
                ]);

                // Über den "mails"-Rate-Limiter gedrosselt versenden (siehe
                // AppServiceProvider), damit dies sich das stündliche
                // Hoster-Limit mit den "Anmeldung möglich"-Mails teilt statt
                // es unabhängig davon zu überschreiten.
                SendErinnerungVerkaeuferMailJob::dispatch($mailLog->id);

                $bereitsProtokolliert[] = $Interessent->mail;
            }
        }
    }
}
