<?php

namespace App\Http\Controllers;

use App\Model\Warteliste;
use App\Repositories\Mails\MailRepository;
use App\Services\AuditLogger;

class WartelistenAngebotController extends Controller
{
    public function __construct(private MailRepository $mailRepository)
    {
    }

    public function confirm(string $token)
    {
        $eintrag = Warteliste::where('token', $token)->first();

        if (! $eintrag) {
            abort(404, 'Dieser Bestätigungslink ist ungültig.');
        }

        if ($eintrag->bestaetigt_at) {
            return view('warteliste.bereits-bestaetigt');
        }

        if (! $eintrag->angebot_ablauf_at || $eintrag->angebot_ablauf_at->isPast()) {
            return view('warteliste.abgelaufen');
        }

        $vknummer = $eintrag->angeboteneVknummer;

        if (! $vknummer) {
            abort(404, 'Für dieses Angebot liegt keine VK-Nummer mehr vor.');
        }

        $vknummer->vergeben_an = $eintrag->interessenten_id;
        $vknummer->save();

        $eintrag->bestaetigt_at = now();
        $eintrag->save();

        AuditLogger::log('warteliste.nachgerueckt', $vknummer, [
            'vknummer' => $vknummer->vknummer,
            'interessent_id' => $eintrag->interessenten_id,
        ]);

        $this->mailRepository->sendVerkaeuferInfo($vknummer);

        $eintrag->delete();

        return view('warteliste.bestaetigt', [
            'vknummer' => $vknummer,
        ]);
    }
}
