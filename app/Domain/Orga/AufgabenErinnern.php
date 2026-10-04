<?php

namespace App\Domain\Orga;

use App\Domain\Kommunikation\Postausgang;
use App\Models\Aufgabe;
use App\Support\Einstellungen;

/** Erinnert Zuständige an bald fällige oder überfällige Aufgaben – eine Sammelmail pro Person. */
class AufgabenErinnern
{
    public function __invoke(): int
    {
        $tage = (int) Einstellungen::get('erinnerung_aufgaben_tage');

        $aufgaben = Aufgabe::query()
            ->offen()
            ->whereNull('erinnert_at')
            ->whereNotNull('zustaendig_id')
            ->whereDate('faellig_am', '<=', today()->addDays($tage))
            ->with(['zustaendig', 'boerse'])
            ->orderBy('faellig_am')
            ->get()
            ->groupBy('zustaendig_id');

        $mails = 0;
        foreach ($aufgaben as $liste) {
            $person = $liste->first()->zustaendig;
            $text = $liste->map(fn (Aufgabe $a) => sprintf(
                '- %s (fällig %s%s)',
                $a->titel,
                $a->faellig_am->format('d.m.Y'),
                $a->boerse ? ', '.$a->boerse->titel : '',
            ))->implode("\n");

            if (Postausgang::einplanen($person, 'aufgabe_erinnerung', null, [
                'aufgaben' => $text,
                'aufgaben_link' => route('admin.aufgaben.index', ['meine' => 1]),
            ])) {
                $mails++;
            }

            Aufgabe::query()->whereKey($liste->pluck('id'))->update(['erinnert_at' => now()]);
        }

        return $mails;
    }
}
