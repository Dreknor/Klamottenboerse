<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerkaufsartikelRequest;
use App\Model\Interessenten;
use App\Model\Verkaufsartikel;
use Illuminate\Http\Request;

class VerkaeuferPortalController extends Controller
{
    /**
     * Findet den Interessenten anhand der UUID und stellt sicher, dass er
     * für die aktuelle Klamottenbörse eine VK-Nummer zugewiesen bekommen hat.
     * Nutzt dasselbe token-basierte Zugriffsmuster wie ErgebnisController,
     * da Verkäufer über keinen klassischen Login-Account verfügen.
     */
    private function findInteressentOrFail(string $uuid): Interessenten
    {
        $interessent = Interessenten::query()->where('uuid', $uuid)->first();

        if (! $interessent || ! $interessent->vknummern_vergeben) {
            abort(404, 'Der Link ist ungültig oder es liegt keine VK-Nummer für die aktuelle Klamottenbörse vor.');
        }

        return $interessent;
    }

    public function index(string $uuid)
    {
        $interessent = $this->findInteressentOrFail($uuid);
        $vknummer = $interessent->vknummern_vergeben;

        $artikel = Verkaufsartikel::where('vknummer_id', $vknummer->id)
            ->orderBy('artikelnummer')
            ->get();

        $liveVerkaeufeFreigegeben = (bool) optional($vknummer->Klamottenboerse)->live_verkaufsansicht_freigabe;
        $verkaufteArtikel = $liveVerkaeufeFreigegeben
            ? $vknummer->verkaufteArtikel()->withoutGlobalScopes()->get()
            : collect();
        $aktuellerErloes = $verkaufteArtikel->sum('betrag');

        return view('verkaeufer-portal.index', [
            'uuid' => $uuid,
            'interessent' => $interessent,
            'vknummer' => $vknummer,
            'artikel' => $artikel,
            'liveVerkaeufeFreigegeben' => $liveVerkaeufeFreigegeben,
            'verkaufteArtikel' => $verkaufteArtikel,
            'aktuellerErloes' => $aktuellerErloes,
        ]);
    }

    public function store(string $uuid, StoreVerkaufsartikelRequest $request)
    {
        $interessent = $this->findInteressentOrFail($uuid);
        $vknummer = $interessent->vknummern_vergeben;

        Verkaufsartikel::create([
            'vknummer_id' => $vknummer->id,
            'artikelnummer' => Verkaufsartikel::naechsteArtikelnummer($vknummer->id),
            'beschreibung' => $request->input('beschreibung'),
            'kategorie' => $request->input('kategorie'),
            'groesse' => $request->input('groesse'),
            'preis' => $request->input('preis'),
        ]);

        return redirect()
            ->route('verkaeuferPortal.index', ['uuid' => $uuid])
            ->with('success', 'Artikel wurde hinzugefügt.');
    }

    public function destroy(string $uuid, Verkaufsartikel $artikel)
    {
        $interessent = $this->findInteressentOrFail($uuid);
        $vknummer = $interessent->vknummern_vergeben;

        if ($artikel->vknummer_id !== $vknummer->id) {
            abort(403);
        }

        $artikel->delete();

        return redirect()
            ->route('verkaeuferPortal.index', ['uuid' => $uuid])
            ->with('success', 'Artikel wurde entfernt.');
    }

    public function etiketten(string $uuid)
    {
        $interessent = $this->findInteressentOrFail($uuid);
        $vknummer = $interessent->vknummern_vergeben;

        $artikel = Verkaufsartikel::where('vknummer_id', $vknummer->id)
            ->orderBy('artikelnummer')
            ->get();

        return view('verkaeufer-portal.etiketten', [
            'interessent' => $interessent,
            'vknummer' => $vknummer,
            'artikel' => $artikel,
        ]);
    }
}
