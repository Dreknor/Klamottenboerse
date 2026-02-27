<?php

namespace App\Console\Commands;

use App\Mail\AnmeldungMoeglichMail;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\Mailvorlagen;
use App\Repositories\Mails\MailRepository;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
    protected $description = 'Sendet gestaffelt Anmeldungs-E-Mails (max. 55/Stunde) an alle Interessenten ohne VK-Nummer für die nächste Klamottenbörse.';

    /** Maximale Mails pro Stunde (Puffer unter dem Hoster-Limit von 60) */
    const MAILS_PRO_STUNDE = 55;

    public function __construct(private MailRepository $mailRepository)
    {
        parent::__construct();
    }

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

        $mailvorlage = Mailvorlagen::where('name', 'AnmeldungMoeglich')->first();

        if (! $mailvorlage) {
            $this->error('Mailvorlage "AnmeldungMoeglich" nicht gefunden.');
            return Command::FAILURE;
        }

        // Alle Interessenten mit E-Mail-Adresse, die noch keine VK-Nummer für die aktuelle Börse haben
        $interessenten = Interessenten::where('mail', '<>', '')
            ->doesntHave('vknummern_vergeben')
            ->get()
            ->unique('mail');

        $gesamt = $interessenten->count();

        if ($gesamt === 0) {
            $this->info('Keine Interessenten ohne VK-Nummer gefunden. Kein Versand nötig.');
            return Command::SUCCESS;
        }

        $this->info("Verteile {$gesamt} Mails in Batches von " . self::MAILS_PRO_STUNDE . " (max. pro Stunde) ...");

        $batches = $interessenten->chunk(self::MAILS_PRO_STUNDE);
        $versendet = 0;

        foreach ($batches as $batchIndex => $batch) {
            // Jeder Batch wird um batchIndex * 60 Minuten verzögert
            $delayMinuten = $batchIndex * 60;
            $versandZeit = Carbon::now()->addMinutes($delayMinuten);

            foreach ($batch as $interessent) {
                $vorlage = $this->mailRepository->replaceInMailvorlage(clone $mailvorlage, $interessent, $klamottenboerse);

                Mail::to($interessent->mail)
                    ->later(
                        $versandZeit,
                        new AnmeldungMoeglichMail($interessent, $vorlage->betreff, $vorlage->text, $vorlage->html)
                    );

                $versendet++;
            }

            $this->line("  Batch " . ($batchIndex + 1) . ": {$batch->count()} Mails eingeplant für " . $versandZeit->format('d.m.Y H:i') . " Uhr");
        }

        $this->info("✓ {$versendet} Mails erfolgreich in die Queue eingestellt.");
        $this->info("  Benötigte Zeit bei max. " . self::MAILS_PRO_STUNDE . " Mails/Stunde: ca. " . ($batches->count() - 1) . " Stunde(n) Gesamtlaufzeit.");

        return Command::SUCCESS;
    }
}
