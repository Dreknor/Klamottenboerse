<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Aufgabe;
use App\Models\Person;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Börsen-Checkliste und allgemeine Team-Aufgaben mit Zuständigkeit und Erinnerung. */
class AufgabeController extends Controller
{
    public function index(Request $request, BoerseKontext $kontext): View
    {
        $boerse = $kontext->get();
        $ansicht = $request->query('ansicht', 'boerse'); // boerse | team | alle

        $aufgaben = Aufgabe::query()
            ->with(['zustaendig', 'erledigtVon', 'boerse'])
            ->when($ansicht === 'boerse', fn ($q) => $q->where('boerse_id', $boerse?->id))
            ->when($ansicht === 'team', fn ($q) => $q->whereNull('boerse_id'))
            ->when($request->boolean('meine'), fn ($q) => $q->where('zustaendig_id', $request->user()->id))
            ->when(! $request->boolean('erledigte'), fn ($q) => $q->whereNull('erledigt_at'))
            ->orderByRaw('erledigt_at is not null')
            ->orderByRaw('faellig_am is null')->orderBy('faellig_am')->orderBy('sortierung')
            ->get();

        return view('admin.aufgaben.index', [
            'boerse' => $boerse,
            'ansicht' => $ansicht,
            'aufgaben' => $aufgaben,
            'team' => Person::query()->whereHas('roles')->orderBy('vorname')->get(),
        ]);
    }

    public function store(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $this->validiert($request);
        $daten['boerse_id'] = $request->boolean('fuer_boerse') ? $kontext->get()?->id : null;
        Aufgabe::create($daten);

        return back()->with('erfolg', 'Aufgabe angelegt.');
    }

    public function update(Request $request, Aufgabe $aufgabe): RedirectResponse
    {
        $daten = $this->validiert($request);
        if ($aufgabe->zustaendig_id !== ($daten['zustaendig_id'] ?? null) || $aufgabe->faellig_am?->toDateString() !== ($daten['faellig_am'] ?? null)) {
            $daten['erinnert_at'] = null; // neue Erinnerung für neue Zuständigkeit oder Fälligkeit
        }
        $aufgabe->update($daten);

        return back()->with('erfolg', 'Aufgabe gespeichert.');
    }

    public function erledigt(Request $request, Aufgabe $aufgabe): RedirectResponse
    {
        $aufgabe->update($aufgabe->erledigt_at
            ? ['erledigt_at' => null, 'erledigt_von' => null]
            : ['erledigt_at' => now(), 'erledigt_von' => $request->user()->id]);

        return back();
    }

    public function destroy(Aufgabe $aufgabe): RedirectResponse
    {
        $aufgabe->delete();

        return back()->with('erfolg', 'Aufgabe gelöscht.');
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request): array
    {
        return $request->validate([
            'titel' => ['required', 'string', 'max:190'],
            'beschreibung' => ['nullable', 'string', 'max:5000'],
            'phase' => ['nullable', 'string', 'max:60'],
            'faellig_am' => ['nullable', 'date'],
            'zustaendig_id' => ['nullable', 'exists:personen,id'],
        ]);
    }
}
