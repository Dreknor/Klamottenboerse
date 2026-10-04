<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aufgabe;
use App\Models\Boerse;
use App\Models\Protokoll;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProtokollController extends Controller
{
    public function index(Request $request): View
    {
        $suche = trim((string) $request->query('suche'));

        return view('admin.protokolle.index', [
            'protokolle' => Protokoll::query()->with('boerse', 'autor')
                ->when($suche !== '', fn ($q) => $q->where(fn ($w) => $w->where('titel', 'like', "%{$suche}%")
                    ->orWhere('inhalt', 'like', "%{$suche}%")->orWhere('teilnehmende', 'like', "%{$suche}%")))
                ->when($request->query('boerse'), fn ($q, $id) => $q->where('boerse_id', $id))
                ->latest('datum')->paginate(30)->withQueryString(),
            'boersen' => Boerse::query()->orderByDesc('verkaufstag')->pluck('titel', 'id'),
            'suche' => $suche,
        ]);
    }

    public function create(BoerseKontext $kontext): View
    {
        return view('admin.protokolle.form', [
            'protokoll' => new Protokoll([
                'datum' => today(),
                'boerse_id' => $kontext->get()?->id,
                'titel' => 'Orga-Treffen '.today()->format('d.m.Y'),
                'inhalt' => "## Themen\n\n- \n\n## Beschlüsse\n\nBeschluss: \n\n## Offene Punkte\n\n- [ ] ",
            ]),
            'boersen' => Boerse::query()->orderByDesc('verkaufstag')->pluck('titel', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $protokoll = Protokoll::create($this->validiert($request) + ['autor_id' => $request->user()->id]);

        return redirect()->route('admin.protokolle.show', $protokoll)->with('erfolg', 'Protokoll gespeichert.');
    }

    public function show(Protokoll $protokoll): View
    {
        return view('admin.protokolle.show', ['protokoll' => $protokoll->load('boerse', 'autor')]);
    }

    public function edit(Protokoll $protokoll): View
    {
        return view('admin.protokolle.form', [
            'protokoll' => $protokoll,
            'boersen' => Boerse::query()->orderByDesc('verkaufstag')->pluck('titel', 'id'),
        ]);
    }

    public function update(Request $request, Protokoll $protokoll): RedirectResponse
    {
        $protokoll->update($this->validiert($request));

        return redirect()->route('admin.protokolle.show', $protokoll)->with('erfolg', 'Protokoll gespeichert.');
    }

    public function destroy(Protokoll $protokoll): RedirectResponse
    {
        $protokoll->delete();

        return redirect()->route('admin.protokolle.index')->with('erfolg', 'Protokoll gelöscht.');
    }

    /** Aus einem offenen Punkt ("- [ ] …") eine Aufgabe machen; der Punkt wird im Protokoll abgehakt. */
    public function aufgabe(Request $request, Protokoll $protokoll): RedirectResponse
    {
        $zeile = $request->integer('zeile');
        $punkte = $protokoll->offenePunkte();
        abort_unless(isset($punkte[$zeile]), 404);

        Aufgabe::create([
            'boerse_id' => $protokoll->boerse_id,
            'titel' => mb_substr($punkte[$zeile], 0, 190),
            'beschreibung' => 'Aus dem Protokoll „'.$protokoll->titel.'“',
            'zustaendig_id' => $request->input('zustaendig_id') ?: null,
            'faellig_am' => $request->input('faellig_am') ?: null,
        ]);

        $zeilen = preg_split('/\R/', (string) $protokoll->inhalt);
        $zeilen[$zeile] = preg_replace('/\[ \]/', '[x]', $zeilen[$zeile], 1).' (→ Aufgabe)';
        $protokoll->update(['inhalt' => implode("\n", $zeilen)]);

        return back()->with('erfolg', 'Aufgabe angelegt.');
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request): array
    {
        return $request->validate([
            'titel' => ['required', 'string', 'max:190'],
            'datum' => ['required', 'date'],
            'boerse_id' => ['nullable', 'exists:boersen,id'],
            'teilnehmende' => ['nullable', 'string', 'max:500'],
            'inhalt' => ['nullable', 'string', 'max:200000'],
        ]);
    }
}
