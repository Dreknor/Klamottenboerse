<?php

namespace App\Console\Commands;

use App\Jobs\SendSchichtErinnerungMailJob;
use App\Model\Appointment;
use App\Model\MailLog;
use Illuminate\Console\Command;

class SchichtErinnerungVersenden extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schicht:erinnerung-versenden {--hours=48 : Erinnerung an Schichten, die innerhalb der nächsten X Stunden beginnen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Versendet automatische Erinnerungs-Mails an Helfer vor ihrer Aufbau-, Börsendienst- oder Abbau-Schicht.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stunden = (int) $this->option('hours');

        $termine = Appointment::query()
            ->whereNotNull('helfer_id')
            ->whereNull('erinnerung_versendet_at')
            ->whereBetween('date_start', [now(), now()->addHours($stunden)])
            ->with('helfer')
            ->get();

        $versendet = 0;

        foreach ($termine as $termin) {
            if (! $termin->helfer || ! $termin->helfer->mail) {
                continue;
            }

            $mailLog = MailLog::create([
                'helfer_id' => $termin->helfer_id,
                'klamottenboerse_id' => $termin->klamottenboerse_id,
                'typ' => 'schichtErinnerung',
                'email' => $termin->helfer->mail,
                'status' => MailLog::STATUS_QUEUED,
            ]);

            SendSchichtErinnerungMailJob::dispatch($mailLog->id);

            $termin->erinnerung_versendet_at = now();
            $termin->save();

            $versendet++;
        }

        $this->info("{$versendet} Schicht-Erinnerung(en) eingeplant.");

        return self::SUCCESS;
    }
}
