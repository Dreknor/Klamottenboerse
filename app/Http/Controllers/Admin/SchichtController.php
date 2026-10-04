<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Schichten\HelferEintragen;
use App\Http\Controllers\Controller;
use App\Models\Einteilung;
use App\Models\Person;
use App\Models\Schicht;
use App\Support\BoerseKontext;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchichtController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();

        return view('admin.schichten.index', [
            'boerse' => $boerse,
            'schichten' => $boerse->schichten()->with(['einteilungen' => fn ($q) => $q->with('person')->orderBy('status')])->get()->groupBy(fn ($s) => $s->beginn->isoFormat('dddd, D. MMMM')),
            'personen' => Person::query()->orderBy('nachname')->get(['id', 'vorname', 'nachname', 'email']),
        ]);
    }

    public function store(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $request->validate([
            'bereich' => ['required', 'string', 'max:60'],
            'beschreibung' => ['nullable', 'string', 'max:190'],
            'datum' => ['required', 'date'],
            'von' => ['required', 'date_format:H:i'],
            'bis' => ['required', 'date_format:H:i', 'after:von'],
            'soll' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $kontext->getOrFail()->schichten()->create([
            'bereich' => $daten['bereich'],
            'beschreibung' => $daten['beschreibung'] ?? null,
            'beginn' => $daten['datum'].' '.$daten['von'],
            'ende' => $daten['datum'].' '.$daten['bis'],
            'soll' => $daten['soll'],
        ]);

        return back()->with('erfolg', 'Schicht angelegt.');
    }

    public function destroy(Schicht $schicht): RedirectResponse
    {
        if ($schicht->zusagen()->exists()) {
            return back()->with('fehler', 'In dieser Schicht sind noch Helfer eingetragen.');
        }
        $schicht->delete();

        return back()->with('erfolg', 'Schicht gelöscht.');
    }

    /** Helfer manuell eintragen: bestehende Person oder neue Person – dafür reicht ein Name. */
    public function helferEintragen(Request $request, Schicht $schicht, HelferEintragen $eintragen): RedirectResponse
    {
        if ($request->filled('person_id')) {
            $person = Person::findOrFail($request->integer('person_id'));
        } else {
            $daten = $request->validate([
                'vorname' => ['required', 'string', 'max:100'],
                'nachname' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:190', Rule::unique('personen', 'email')],
                'telefon' => ['nullable', 'string', 'max:50'],
            ]);
            $person = Person::create($daten);
        }

        try {
            $eintragen($schicht, $person, 'orga', $request->boolean('mail'));
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', "{$person->name} ist eingetragen.");
    }

    public function helferEntfernen(Einteilung $einteilung): RedirectResponse
    {
        $einteilung->delete();

        return back()->with('erfolg', 'Helfer aus der Schicht entfernt.');
    }
}
