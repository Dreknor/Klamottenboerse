<?php

namespace App\Console\Commands;

use App\Jobs\SendAnmeldungMoeglichMailJob;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\MailLog;
use App\Model\Mailvorlagen;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAnmeldungMoeglichMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:anmeldung-moeglich
                            {--force : Versand auch ohne Datumscheck erzwingen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stellt die Anmeldungs-E-Mails an alle Interessenten ohne VK-Nummer für die nächste Klamottenbörse in die Warteschlange. Der tatsächliche Versand wird über einen Rate-Limiter auf max. 55 Mails/Stunde gedrosselt.';

    /** Name der Mailvorlage / des Mail-Log-Typs */
    const TYP = 'AnmeldungMoeglich';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if (! $klamottenboerse) {
            $this->error('Keine Klamottenbörse gefunden.');
            return Command::FAILURE;
        }

        // Datumscheck: Nur am Anmeldungs-Tag versenden, außer --force ist gesetzt
        $istAnmeldungsTag = $klamottenboerse->anmeldung->format('d.m.Y') === Carbon::now()->format('d.m.Y');

        if (! $istAnmeldungsTag && ! $this->option('force')) {
            $this->info('Heute ist kein Anmeldungs-Tag. Kein Versand. (--force zum Erzwingen nutzen)');
            return Command::SUCCESS;
        }

        if (! $klamottenboerse->sendInvitation && ! $this->option('force')) {
            $this->info('sendInvitation ist deaktiviert. Kein Versand.');
            return Command::SUCCESS;
        }

        if (! Mailvorlagen::where('name', self::TYP)->exists()) {
            $this->error('Mailvorlage "' . self::TYP . '" nicht gefunden.');
            return Command::FAILURE;
        }

        // Alle Interessenten mit E-Mail-Adresse, die noch keine VK-Nummer für die aktuelle Börse haben
        $interessenten = Interessenten::where('mail', '<>', '')
            ->doesntHave('vknummern_vergeben')
            ->get()
            ->unique('mail');

        if ($interessenten->isEmpty()) {
            $this->info('Keine Interessenten ohne VK-Nummer gefunden. Kein Versand nötig.');
            return Command::SUCCESS;
        }

        // Bereits protokollierte (versendete oder noch offene) Mails für diese Börse
        // nicht erneut einplanen, damit ein wiederholter Aufruf des Kommandos keine
        // doppelten Mails erzeugt.
        $bereitsProtokolliert = MailLog::typ(self::TYP)
            ->where('klamottenboerse_id', $klamottenboerse->id)
            ->pluck('email')
            ->all();

        $neueInteressenten = $interessenten->reject(
            fn ($interessent) => in_array($interessent->mail, $bereitsProtokolliert, true)
        );

        if ($neueInteressenten->isEmpty()) {
            $this->info('Für alle Interessenten wurde bereits eine Mail eingeplant. Nutze das Frontend, um fehlgeschlagene Mails erneut zu versenden.');
            return Command::SUCCESS;
        }

        $eingeplant = 0;

        foreach ($neueInteressenten as $interessent) {
            $mailLog = MailLog::create([
                'interessent_id' => $interessent->id,
                'klamottenboerse_id' => $klamottenboerse->id,
                'typ' => self::TYP,
                'email' => $interessent->mail,
                'status' => MailLog::STATUS_QUEUED,
            ]);

            SendAnmeldungMoeglichMailJob::dispatch($mailLog->id);

            $eingeplant++;
        }

        $this->info("✓ {$eingeplant} Mails wurden in die Warteschlange eingestellt (max. 55 Mails/Stunde werden tatsächlich versendet).");
        $this->info('  Status und Versandprotokoll sind im Frontend unter "Mail-Protokoll" einsehbar.');

        return Command::SUCCESS;
    }
}
