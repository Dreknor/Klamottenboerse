<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVermerkRequest;
use App\Model\Interessenten;
use App\Model\VerkaeuferVermerk;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Verkäufer-Reputation: Erfassung von Vermerken (schnell von Kasse und
 * Kistenerfassung aus) sowie Übersicht und Pflege durch das Orga-Team.
 */
class VermerkeController extends Controller
{
    public function __construct(private KlamottenboersenRepository $klamottenboersenRepository)
    {
    }

    /**
     * Übersicht aller Interessenten mit Vermerken (nur Verwaltung).
     */
    public function index()
    {
        $interessenten = Interessenten::query()
            ->where(function ($query) {
                $query->whereHas('vermerke')->orWhereNotNull('nur_manuelle_vergabe');
            })
            ->withReputationPunkte()
            ->with(['vermerke' => fn ($q) => $q->latest()->with('vknummer', 'erfasstVon')])
            ->get()
            ->sortByDesc(fn (Interessenten $i) => $i->reputationPunkte());

        return view('vermerke.index', [
            'interessenten' => $interessenten,
        ]);
    }

    /**
     * Eigenständige Schnellerfassung (z. B. aus der Kassen-Navigation).
     */
    public function create(Request $request)
    {
        return view('vermerke.create', [
            'vknummer' => $request->query('vknummer'),
            'quelle' => Gate::allows('access-verwaltung') ? VerkaeuferVermerk::QUELLE_VERWALTUNG : VerkaeuferVermerk::QUELLE_KASSE,
        ]);
    }

    public function store(StoreVermerkRequest $request)
    {
        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        if ($request->filled('interessent_id')) {
            $interessent = Interessenten::findOrFail($request->input('interessent_id'));
            $vknummer = $interessent->vknummern_vergeben;
        } else {
            $vknummer = VKnummer::query()
                ->where('klamottenboersen_id', optional($klamottenboerse)->id)
                ->where('vknummer', $request->input('vknummer'))
                ->whereNotNull('vergeben_an')
                ->first();

            if (! $vknummer || ! $vknummer->vergeben_an_Interessent) {
                return redirect()->back()->withInput()->with('error', 'Verkäufernummer '.$request->input('vknummer').' ist in der aktuellen Börse nicht vergeben.');
            }

            $interessent = $vknummer->vergeben_an_Interessent;
        }

        // Kassen-Accounts nutzen immer die Standardpunkte; nur die Verwaltung darf gewichten.
        $punkte = Gate::allows('access-verwaltung') && $request->filled('punkte')
            ? (int) $request->input('punkte')
            : VerkaeuferVermerk::standardPunkte($request->input('typ'));

        $warVorherGesperrt = $interessent->istAutomatischeVergabeGesperrt();

        $vermerk = VerkaeuferVermerk::create([
            'interessent_id' => $interessent->id,
            'klamottenboerse_id' => optional($klamottenboerse)->id,
            'vknummer_id' => optional($vknummer)->id,
            'typ' => $request->input('typ'),
            'punkte' => $punkte,
            'bemerkung' => $request->input('bemerkung'),
            'quelle' => $request->input('quelle'),
            'erfasst_von' => $request->user()->id,
        ]);

        AuditLogger::log('vermerk.erfasst', $vermerk, [
            'interessent_id' => $interessent->id,
            'typ' => $vermerk->typ,
            'punkte' => $vermerk->punkte,
        ]);

        $meldung = 'Vermerk für '.($vknummer ? 'VK-Nr. '.$vknummer->vknummer : $interessent->vorname.' '.$interessent->nachname).' gespeichert.';

        if (! $warVorherGesperrt && $interessent->istAutomatischeVergabeGesperrt()) {
            $meldung .= ' Automatische Nummernvergabe ist jetzt gesperrt.';
        }

        return redirect()->back()->with('success', $meldung);
    }

    public function destroy(VerkaeuferVermerk $vermerk)
    {
        $vermerk->delete();

        AuditLogger::log('vermerk.geloescht', $vermerk, [
            'interessent_id' => $vermerk->interessent_id,
            'typ' => $vermerk->typ,
            'punkte' => $vermerk->punkte,
        ]);

        return redirect()->back()->with('success', 'Vermerk entfernt.');
    }

    /**
     * Manuelle Festlegung des Vergabemodus: automatisch (nach Punkten),
     * immer nur händisch oder trotz Punkten freigegeben.
     */
    public function vergabemodus(Request $request, Interessenten $interessent)
    {
        $request->validate([
            'modus' => 'required|in:auto,manuell,freigegeben',
        ]);

        $interessent->nur_manuelle_vergabe = match ($request->input('modus')) {
            'manuell' => true,
            'freigegeben' => false,
            default => null,
        };
        $interessent->save();

        AuditLogger::log('vermerk.vergabemodus', $interessent, [
            'modus' => $request->input('modus'),
        ]);

        return redirect()->back()->with('success', 'Vergabemodus aktualisiert.');
    }
}
