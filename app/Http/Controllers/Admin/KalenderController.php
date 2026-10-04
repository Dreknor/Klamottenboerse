<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aufgabe;
use App\Models\Boerse;
use App\Models\Schicht;
use App\Models\Termin;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Teamkalender: eigene Termine plus alle Börsen-Termine, Schichten und fällige Aufgaben. */
class KalenderController extends Controller
{
    public function index(Request $request): View
    {
        $monat = CarbonImmutable::createFromFormat('Y-m', $request->query('monat', now()->format('Y-m')))->startOfMonth();
        $von = $monat->startOfWeek();
        $bis = $monat->endOfMonth()->endOfWeek();

        return view('admin.kalender.index', [
            'monat' => $monat,
            'von' => $von,
            'bis' => $bis,
            'eintraege' => $this->eintraege($von, $bis)->groupBy(fn ($e) => $e['datum']->toDateString()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'titel' => ['required', 'string', 'max:190'],
            'beginn' => ['required', 'date'],
            'ende' => ['nullable', 'date', 'after_or_equal:beginn'],
            'ort' => ['nullable', 'string', 'max:190'],
            'beschreibung' => ['nullable', 'string', 'max:2000'],
        ]);
        Termin::create($daten);

        return back()->with('erfolg', 'Termin eingetragen.');
    }

    public function destroy(Termin $termin): RedirectResponse
    {
        $termin->delete();

        return back()->with('erfolg', 'Termin gelöscht.');
    }

    /** @return Collection<int, array{datum: CarbonImmutable, titel: string, art: string, termin?: Termin}> */
    private function eintraege(CarbonImmutable $von, CarbonImmutable $bis): Collection
    {
        $eintraege = collect();

        foreach (Termin::query()->whereBetween('beginn', [$von, $bis->endOfDay()])->get() as $termin) {
            $eintraege->push(['datum' => $termin->beginn->toImmutable(), 'titel' => $termin->beginn->format('H:i').' '.$termin->titel, 'art' => 'termin', 'termin' => $termin]);
        }

        $felder = [
            'anmeldung_kinderhaus_ab' => 'Anmeldestart Kinderhaus', 'anmeldung_ab' => 'Anmeldestart alle',
            'anlieferung_beginn' => 'Annahme der Kisten', 'verkauf_beginn' => 'Verkauf', 'abholung_beginn' => 'Abholung',
        ];
        foreach (Boerse::query()->whereBetween('verkaufstag', [$von->subDays(40), $bis->addDays(2)])->get() as $boerse) {
            foreach ($felder as $feld => $text) {
                $zeit = $boerse->{$feld};
                if ($zeit && $zeit->between($von, $bis->endOfDay())) {
                    $eintraege->push(['datum' => $zeit->toImmutable(), 'titel' => $text, 'art' => 'boerse']);
                }
            }
        }

        Schicht::query()->whereBetween('beginn', [$von, $bis->endOfDay()])->withCount('zusagen')->get()
            ->groupBy(fn ($s) => $s->beginn->toDateString())
            ->each(fn ($liste, $tag) => $eintraege->push([
                'datum' => CarbonImmutable::parse($tag),
                'titel' => $liste->count().' Schicht(en), '.$liste->sum('zusagen_count').'/'.$liste->sum('soll').' besetzt',
                'art' => 'schicht',
            ]));

        foreach (Aufgabe::query()->offen()->whereBetween('faellig_am', [$von, $bis])->get() as $aufgabe) {
            $eintraege->push(['datum' => $aufgabe->faellig_am->toImmutable(), 'titel' => '☐ '.$aufgabe->titel, 'art' => 'aufgabe']);
        }

        return $eintraege->sortBy(fn ($e) => $e['datum']->getTimestamp())->values();
    }
}
