<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKisteRequest;
use App\Model\Kiste;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class KistenController extends Controller
{
    public function __construct(private KlamottenboersenRepository $klamottenboersenRepository)
    {
    }

    /**
     * Übersicht aller Kisten der aktuellen Klamottenbörse.
     */
    public function index()
    {
        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        $kisten = Kiste::where('klamottenboerse_id', $klamottenboerse->id)
            ->with(['vknummer.vergeben_an_Interessent'])
            ->orderByDesc('abgegeben_at')
            ->get();

        return view('kisten.index', [
            'klamottenboerse' => $klamottenboerse,
            'kisten' => $kisten,
            'vknummern' => VKnummer::where('klamottenboersen_id', $klamottenboerse->id)
                ->whereNotNull('vergeben_an')
                ->with('vergeben_an_Interessent')
                ->orderBy('vknummer')
                ->get(),
        ]);
    }

    /**
     * Check-in: Erfasst die Abgabe von N Kisten für eine VK-Nummer.
     */
    public function store(StoreKisteRequest $request)
    {
        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();
        $vknummer = VKnummer::findOrFail($request->vknummer_id);

        $angelegt = [];

        for ($i = 0; $i < $request->anzahl; $i++) {
            $kiste = Kiste::create([
                'klamottenboerse_id' => $klamottenboerse->id,
                'vknummer_id' => $vknummer->id,
                'kistennummer' => Kiste::naechsteKistennummer($vknummer->id),
                'qr_token' => Kiste::generiereQrToken(),
                'status' => Kiste::STATUS_ABGEGEBEN,
                'abgegeben_at' => now(),
                'abgegeben_von' => $request->user()->id,
                'bemerkung' => $request->bemerkung,
            ]);

            AuditLogger::log('kiste.checkin', $kiste, [
                'vknummer' => $vknummer->vknummer,
                'kistennummer' => $kiste->kistennummer,
            ]);

            $angelegt[] = $kiste;
        }

        return redirect()->route('kisten.index')->with('success', count($angelegt).' Kiste(n) erfolgreich eingecheckt.');
    }

    /**
     * Check-out: Markiert eine Kiste als abgeholt.
     */
    public function checkout(Request $request, Kiste $kiste)
    {
        if ($request->user()->verwaltung != 1) {
            return redirect()->back()->with('error', 'Berechtigung fehlt');
        }

        if ($kiste->istAbgeholt()) {
            return redirect()->route('kisten.index')->with('error', 'Diese Kiste wurde bereits abgeholt.');
        }

        $kiste->update([
            'status' => Kiste::STATUS_ABGEHOLT,
            'abgeholt_at' => now(),
            'abgeholt_von' => $request->user()->id,
        ]);

        AuditLogger::log('kiste.checkout', $kiste, [
            'vknummer_id' => $kiste->vknummer_id,
            'kistennummer' => $kiste->kistennummer,
        ]);

        return redirect()->route('kisten.index')->with('success', 'Kiste erfolgreich als abgeholt markiert.');
    }

    /**
     * QR-Code-Scan: Direkter Check-out per Token (z. B. Smartphone-Kamera-App).
     */
    public function scan(Request $request, string $qrToken)
    {
        if ($request->user()->verwaltung != 1) {
            return redirect()->back()->with('error', 'Berechtigung fehlt');
        }

        $kiste = Kiste::where('qr_token', $qrToken)->firstOrFail();

        if ($kiste->istAbgeholt()) {
            return redirect()->route('kisten.index')->with('error', 'Diese Kiste wurde bereits abgeholt.');
        }

        $kiste->update([
            'status' => Kiste::STATUS_ABGEHOLT,
            'abgeholt_at' => now(),
            'abgeholt_von' => $request->user()->id,
        ]);

        AuditLogger::log('kiste.checkout', $kiste, [
            'vknummer_id' => $kiste->vknummer_id,
            'kistennummer' => $kiste->kistennummer,
            'via' => 'qr-scan',
        ]);

        return redirect()->route('kisten.index')->with('success', "Kiste #{$kiste->kistennummer} per QR-Scan als abgeholt markiert.");
    }
}
