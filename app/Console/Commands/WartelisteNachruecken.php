<?php

namespace App\Console\Commands;

use App\Mail\WartelisteAngebotMail;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Model\Warteliste;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class WartelisteNachruecken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warteliste:nachruecken {--hours=48 : Zeitfenster in Stunden zur Bestätigung des Angebots}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bietet freie VK-Nummern automatisch der/dem nächsten auf der Warteliste per E-Mail an (mit Bestätigungsfrist).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $klamottenboerse = Klamottenboerse::orderByDesc('datum')->first();

        if (! $klamottenboerse) {
            $this->error('Keine Klamottenbörse gefunden.');

            return self::FAILURE;
        }

        $stundenFrist = (int) $this->option('hours');

        $freieNummern = VKnummer::query()
            ->whereNull('vergeben_an')
            ->whereNull('reserviert_fuer')
            ->where('klamottenboersen_id', $klamottenboerse->id)
            ->orderBy('vknummer')
            ->get();

        if ($freieNummern->isEmpty()) {
            $this->info('Keine freien VK-Nummern verfügbar.');

            return self::SUCCESS;
        }

        // Wartelisten-Einträge ohne aktives, noch gültiges Angebot, älteste zuerst (FIFO).
        $wartende = Warteliste::query()
            ->where(function ($query) {
                $query->whereNull('angebot_ablauf_at')
                    ->orWhere('angebot_ablauf_at', '<=', now());
            })
            ->whereNull('bestaetigt_at')
            ->orderBy('created_at')
            ->with('Interessent')
            ->get();

        $vergeben = 0;

        foreach ($freieNummern as $vknummer) {
            $kandidat = $wartende->first(function (Warteliste $eintrag) use ($vknummer) {
                if (! $eintrag->Interessent || ! $eintrag->Interessent->mail) {
                    return false;
                }

                $uebersprungen = $eintrag->uebersprungene_vknummern ?? [];

                return ! in_array($vknummer->id, $uebersprungen, true);
            });

            if (! $kandidat) {
                continue;
            }

            $vknummer->reserviert_fuer = $kandidat->interessenten_id;
            $vknummer->save();

            $kandidat->angebotene_vknummer_id = $vknummer->id;
            $kandidat->angebot_versendet_at = now();
            $kandidat->angebot_ablauf_at = now()->addHours($stundenFrist);
            $kandidat->token = Str::random(48);
            $kandidat->save();

            $confirmationUrl = url('/warteliste/'.$kandidat->token.'/bestaetigen');

            Mail::to($kandidat->Interessent->mail)->send(
                new WartelisteAngebotMail($kandidat, $vknummer, $confirmationUrl)
            );

            $wartende = $wartende->reject(fn ($eintrag) => $eintrag->id === $kandidat->id);
            $vergeben++;
        }

        $this->info("{$vergeben} Verkäuferplatz-Angebot(e) versendet.");

        return self::SUCCESS;
    }
}
