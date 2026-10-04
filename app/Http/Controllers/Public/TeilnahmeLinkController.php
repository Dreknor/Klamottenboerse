<?php

namespace App\Http\Controllers\Public;

use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\AngebotAnnehmen;
use App\Http\Controllers\Controller;
use App\Models\Teilnahme;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Selbstbedienung per signiertem Link aus der Mail: Absage und Annahme eines Warteliste-Angebots. */
class TeilnahmeLinkController extends Controller
{
    public function absage(Teilnahme $teilnahme): View
    {
        return view('public.absage', ['teilnahme' => $teilnahme->load(['boerse', 'person']), 'erledigt' => false]);
    }

    public function absagen(Request $request, Teilnahme $teilnahme, Absagen $absagen): View
    {
        try {
            $absagen($teilnahme, 'verkaeufer');
        } catch (DomainException $e) {
            return view('public.hinweis', ['titel' => 'Absage nicht möglich', 'text' => $e->getMessage()]);
        }

        return view('public.absage', ['teilnahme' => $teilnahme->fresh(['boerse', 'person']), 'erledigt' => true]);
    }

    public function angebot(Teilnahme $teilnahme): View
    {
        return view('public.angebot', ['teilnahme' => $teilnahme->load(['boerse', 'person'])]);
    }

    public function annehmen(Teilnahme $teilnahme, AngebotAnnehmen $annehmen): View
    {
        try {
            $annehmen($teilnahme);
        } catch (DomainException $e) {
            return view('public.hinweis', ['titel' => 'Angebot abgelaufen', 'text' => $e->getMessage()]);
        }

        return view('public.hinweis', [
            'titel' => 'Du bist dabei!',
            'text' => "Deine Verkäufernummer ist {$teilnahme->nummer}. Alle Infos kommen gleich per Mail.",
        ]);
    }
}
