<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Models\Kategorie;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Kategorien pflegen („Was bringen Verkäufer mit?“) und sehen, wie sich das Angebot
 * der aktuellen Börse verteilt – hilfreich für die Planung der Tische.
 */
class KategorieController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->get();
        $teilnahmen = $boerse?->teilnahmen()->mitNummer()->pluck('person_id') ?? collect();

        $verkaeufer = DB::table('kategorie_person')->whereIn('person_id', $teilnahmen)
            ->selectRaw('kategorie_id, count(*) as anzahl')->groupBy('kategorie_id')->pluck('anzahl', 'kategorie_id');
        $artikel = $boerse
            ? Artikel::query()->whereHas('teilnahme', fn ($q) => $q->where('boerse_id', $boerse->id))
                ->selectRaw('kategorie_id, count(*) as anzahl')->groupBy('kategorie_id')->pluck('anzahl', 'kategorie_id')
            : collect();
        $ohneAngabe = $teilnahmen->isEmpty() ? 0 : $teilnahmen->diff(DB::table('kategorie_person')->whereIn('person_id', $teilnahmen)->pluck('person_id'))->count();

        return view('admin.kategorien.index', [
            'boerse' => $boerse,
            'kategorien' => Kategorie::query()->sortiert()->withCount('personen')->get(),
            'verkaeufer' => $verkaeufer,
            'artikel' => $artikel,
            'verkaeuferGesamt' => $teilnahmen->count(),
            'ohneAngabe' => $ohneAngabe,
            'gruppen' => Kategorie::query()->distinct()->orderBy('gruppe')->pluck('gruppe'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Kategorie::create($this->validiert($request) + ['sortierung' => (int) Kategorie::query()->max('sortierung') + 10, 'aktiv' => true]);

        return back()->with('erfolg', 'Kategorie angelegt. Sie ist ab sofort bei der Anmeldung auswählbar.');
    }

    public function update(Request $request, Kategorie $kategorie): RedirectResponse
    {
        $kategorie->update($this->validiert($request) + [
            'sortierung' => $request->integer('sortierung'),
            'aktiv' => $request->boolean('aktiv'),
        ]);

        return back()->with('erfolg', "„{$kategorie->name}“ gespeichert.");
    }

    public function destroy(Kategorie $kategorie): RedirectResponse
    {
        if ($kategorie->artikel()->exists()) {
            return back()->with('fehler', "„{$kategorie->name}“ ist schon Artikeln zugeordnet. Bitte statt Löschen auf „nicht mehr auswählbar“ stellen.");
        }
        $kategorie->delete();

        return back()->with('erfolg', 'Kategorie gelöscht.');
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request): array
    {
        $daten = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gruppe' => ['required', 'string', 'max:60'],
            'groesse_von' => ['nullable', 'integer', 'min:0', 'max:999', 'required_with:groesse_bis'],
            'groesse_bis' => ['nullable', 'integer', 'min:0', 'max:999', 'required_with:groesse_von', 'gte:groesse_von'],
        ], [
            'groesse_bis.gte' => 'Die Größe „bis“ muss mindestens so groß sein wie „von“.',
            'groesse_von.required_with' => 'Für den Größenbereich bitte „von“ und „bis“ ausfüllen.',
            'groesse_bis.required_with' => 'Für den Größenbereich bitte „von“ und „bis“ ausfüllen.',
        ]);

        return $daten + ['groesse_von' => null, 'groesse_bis' => null];
    }
}
