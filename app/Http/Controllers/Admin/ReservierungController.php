<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nummernreservierung;
use App\Models\Person;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Fest reservierte Nummern für bestimmte Verkäufer – dauerhaft oder für eine Börse. */
class ReservierungController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        return view('admin.reservierungen.index', [
            'boerse' => $kontext->get(),
            'reservierungen' => Nummernreservierung::query()->with(['person', 'boerse'])->orderBy('nummer')->get(),
            'personen' => Person::query()->orderBy('nachname')->orderBy('vorname')->get(['id', 'vorname', 'nachname', 'email']),
        ]);
    }

    public function store(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $boerse = $kontext->get();
        $daten = $request->validate([
            'person_id' => ['required', 'exists:personen,id'],
            'nummer' => ['required', 'integer', 'min:1', 'max:999'],
            'dauerhaft' => ['nullable', 'boolean'],
            'grund' => ['nullable', 'string', 'max:190'],
        ]);

        if ($boerse && $daten['nummer'] === $boerse->kinderhaus_nummer) {
            return back()->with('fehler', "Die {$boerse->kinderhaus_nummer} ist die feste Nummer des Kinderhauses.");
        }

        $belegt = Nummernreservierung::query()->where('nummer', $daten['nummer'])
            ->when($boerse, fn ($q) => $q->gueltigFuer($boerse))->exists();
        if ($belegt) {
            return back()->with('fehler', "Die Nummer {$daten['nummer']} ist bereits reserviert.");
        }

        Nummernreservierung::create([
            'person_id' => $daten['person_id'],
            'nummer' => $daten['nummer'],
            'boerse_id' => $request->boolean('dauerhaft') ? null : $boerse?->id,
            'grund' => $daten['grund'] ?? null,
        ]);

        return back()->with('erfolg', 'Nummer reserviert.');
    }

    public function destroy(Nummernreservierung $reservierung): RedirectResponse
    {
        $reservierung->delete();

        return back()->with('erfolg', 'Reservierung aufgehoben.');
    }
}
