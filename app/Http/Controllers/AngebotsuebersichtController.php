<?php

namespace App\Http\Controllers;

use App\Model\Angebotskategorie;
use App\Model\Interessenten;
use App\Model\Verkaufsartikel;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Services\Angebotskategorien;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Übersicht für das Orga-Team zur Vor- und Nachbereitung: Welche Verkäufer
 * der aktuellen Börse bringen überwiegend was mit (Angabe bei der
 * Registrierung bzw. im Portal) und welche Artikel sind bereits erfasst.
 */
class AngebotsuebersichtController extends Controller
{
    public function __construct(private KlamottenboersenRepository $klamottenboersenRepository)
    {
    }

    public function index()
    {
        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        $vknummern = VKnummer::query()
            ->where('klamottenboersen_id', optional($klamottenboerse)->id)
            ->whereNotNull('vergeben_an')
            ->with('vergeben_an_Interessent')
            ->orderBy('vknummer')
            ->get()
            ->filter(fn (VKnummer $vk) => $vk->vergeben_an_Interessent);

        (new EloquentCollection($vknummern->pluck('vergeben_an_Interessent')))
            ->loadSum(['vermerke as reputation_punkte' => fn ($q) => $q->wirksam()], 'punkte');

        $artikelJeVknummer = Verkaufsartikel::query()
            ->whereIn('vknummer_id', $vknummern->pluck('id'))
            ->get()
            ->groupBy('vknummer_id');

        $kategorien = Angebotskategorie::alle()->map(fn (Angebotskategorie $kategorie) => [
            'label' => $kategorie->label.($kategorie->aktiv ? '' : ' (inaktiv)'),
            'aktiv' => $kategorie->aktiv,
            'verkaeufer' => collect(),
            'artikel' => 0,
        ])->all();
        $ohneKategorie = ['verkaeufer' => collect(), 'artikel' => 0];

        $zeilen = $vknummern->map(function (VKnummer $vk) use (&$kategorien, &$ohneKategorie, $artikelJeVknummer) {
            $interessent = $vk->vergeben_an_Interessent;
            $angegeben = collect($interessent->angebotskategorien ?? [])->filter(fn ($key) => isset($kategorien[$key]));

            foreach ($angegeben as $key) {
                $kategorien[$key]['verkaeufer']->push($vk);
            }

            if ($angegeben->isEmpty()) {
                $ohneKategorie['verkaeufer']->push($vk);
            }

            $artikel = $artikelJeVknummer->get($vk->id, collect());
            $artikelKategorien = [];

            foreach ($artikel as $einArtikel) {
                $key = Angebotskategorien::fuerArtikel($einArtikel);

                if ($key) {
                    $kategorien[$key]['artikel']++;
                    $artikelKategorien[$key] = ($artikelKategorien[$key] ?? 0) + 1;
                } else {
                    $ohneKategorie['artikel']++;
                }
            }

            return [
                'vknummer' => $vk,
                'interessent' => $interessent,
                'kategorien' => $interessent->angebotskategorienLabels(),
                'artikel_anzahl' => $artikel->count(),
                'artikel_kategorien' => $artikelKategorien,
            ];
        });

        return view('angebote.index', [
            'klamottenboerse' => $klamottenboerse,
            'kategorien' => $kategorien,
            'ohneKategorie' => $ohneKategorie,
            'zeilen' => $zeilen,
        ]);
    }

    public function update(Request $request, Interessenten $interessent)
    {
        $daten = $request->validate([
            'angebotskategorien' => 'nullable|array',
            'angebotskategorien.*' => ['string', Rule::in(Angebotskategorien::keys())],
        ]);

        $interessent->angebotskategorien = array_values($daten['angebotskategorien'] ?? []);
        $interessent->save();

        return redirect()->back()->with('success', 'Angebotskategorien gespeichert.');
    }
}
