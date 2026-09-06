<?php

namespace App\Console\Commands;

use App\Model\Warteliste;
use Illuminate\Console\Command;

class WartelisteAngeboteBereinigen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warteliste:angebote-bereinigen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setzt abgelaufene, unbestätigte Wartelisten-Angebote zurück, damit die VK-Nummer der/dem Nächsten angeboten werden kann.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $abgelaufene = Warteliste::query()
            ->whereNotNull('angebotene_vknummer_id')
            ->whereNull('bestaetigt_at')
            ->where('angebot_ablauf_at', '<=', now())
            ->with('angeboteneVknummer')
            ->get();

        foreach ($abgelaufene as $eintrag) {
            $vknummer = $eintrag->angeboteneVknummer;

            if ($vknummer && $vknummer->reserviert_fuer === $eintrag->interessenten_id) {
                $vknummer->reserviert_fuer = null;
                $vknummer->save();
            }

            $uebersprungen = $eintrag->uebersprungene_vknummern ?? [];
            $uebersprungen[] = $eintrag->angebotene_vknummer_id;

            $eintrag->update([
                'angebotene_vknummer_id' => null,
                'angebot_versendet_at' => null,
                'angebot_ablauf_at' => null,
                'token' => null,
                'uebersprungene_vknummern' => array_values(array_unique($uebersprungen)),
            ]);
        }

        $this->info("{$abgelaufene->count()} abgelaufene(s) Angebot(e) zurückgesetzt.");

        return self::SUCCESS;
    }
}
