<?php

namespace App\Http\Controllers;

use App\Console\Commands\SendAnmeldungMoeglichMails;
use App\Jobs\SendAnmeldungMoeglichMailJob;
use App\Model\Klamottenboerse;
use App\Model\MailLog;

class MailLogController extends Controller
{
    /**
     * Zeigt das Versandprotokoll der "Anmeldung möglich"-Mails für die
     * aktuelle Klamottenbörse: wer hat die Mail bereits erhalten, wer
     * wartet noch (Rate-Limit) und bei wem ist der Versand fehlgeschlagen.
     */
    public function anmeldungMoeglich()
    {
        $klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        $mailLogs = collect();

        if ($klamottenboerse) {
            $mailLogs = MailLog::with('interessent')
                ->typ(SendAnmeldungMoeglichMails::TYP)
                ->where('klamottenboerse_id', $klamottenboerse->id)
                ->orderBy('status')
                ->orderBy('id')
                ->get();
        }

        return view('mails.anmeldungMoeglichProtokoll', [
            'Klamottenboerse' => $klamottenboerse,
            'MailLogs' => $mailLogs,
            'AnzahlGesendet' => $mailLogs->where('status', MailLog::STATUS_SENT)->count(),
            'AnzahlOffen' => $mailLogs->where('status', MailLog::STATUS_QUEUED)->count(),
            'AnzahlFehlgeschlagen' => $mailLogs->where('status', MailLog::STATUS_FAILED)->count(),
        ]);
    }

    /**
     * Versendet eine einzelne, noch nicht erfolgreich zugestellte Mail
     * erneut. Sie durchläuft dabei wieder den Rate-Limiter, wodurch das
     * Mail-Limit weiterhin eingehalten wird.
     */
    public function resend(MailLog $mailLog)
    {
        if ($mailLog->status === MailLog::STATUS_SENT) {
            return redirect()->back()->with([
                'Meldung' => 'Diese Mail wurde bereits erfolgreich versendet.',
                'type' => 'info',
            ]);
        }

        $mailLog->update(['status' => MailLog::STATUS_QUEUED, 'fehler' => null]);

        SendAnmeldungMoeglichMailJob::dispatch($mailLog->id);

        return redirect()->back()->with([
            'Meldung' => "Mail an {$mailLog->email} wurde erneut zur Warteschlange hinzugefügt.",
            'type' => 'success',
        ]);
    }

    /**
     * Versendet alle noch nicht erfolgreich zugestellten Mails (offen
     * oder fehlgeschlagen) der aktuellen Klamottenbörse erneut.
     */
    public function resendAll()
    {
        $klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if (! $klamottenboerse) {
            return redirect()->back()->with([
                'Meldung' => 'Keine Klamottenbörse gefunden.',
                'type' => 'danger',
            ]);
        }

        $offeneMailLogs = MailLog::typ(SendAnmeldungMoeglichMails::TYP)
            ->where('klamottenboerse_id', $klamottenboerse->id)
            ->offen()
            ->get();

        foreach ($offeneMailLogs as $mailLog) {
            $mailLog->update(['status' => MailLog::STATUS_QUEUED, 'fehler' => null]);
            SendAnmeldungMoeglichMailJob::dispatch($mailLog->id);
        }

        return redirect()->back()->with([
            'Meldung' => "{$offeneMailLogs->count()} noch nicht zugestellte Mails wurden erneut zur Warteschlange hinzugefügt (max. 55/Stunde werden tatsächlich versendet).",
            'type' => 'success',
        ]);
    }
}
