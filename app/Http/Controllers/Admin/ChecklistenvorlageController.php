<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Boersen\Actions\ChecklisteErzeugen;
use App\Http\Controllers\Controller;
use App\Models\Checklistenvorlage;
use App\Models\ChecklistenvorlageEintrag;
use App\Support\BoerseKontext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Checklisten-Vorlagen: die Standard-Checkliste für jede neue Börse und weitere Vorlagen
 * für Stationen (z. B. "Aufbau Kasse"), die bei Bedarf auf eine Börse angewendet werden.
 */
class ChecklistenvorlageController extends Controller
{
    public function index(Request $request): View
    {
        $vorlagen = Checklistenvorlage::query()->withCount('eintraege')->orderByDesc('fuer_neue_boersen')->orderBy('name')->get();
        if ($vorlagen->isEmpty()) {
            $vorlagen->push(Checklistenvorlage::create(['name' => 'Standard-Checkliste je Börse', 'fuer_neue_boersen' => true]));
        }

        $aktiv = $vorlagen->firstWhere('id', $request->integer('vorlage')) ?? $vorlagen->first();

        return view('admin.aufgaben.vorlage', [
            'vorlagen' => $vorlagen,
            'vorlage' => $aktiv->load('eintraege'),
        ]);
    }

    public function vorlageAnlegen(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'beschreibung' => ['nullable', 'string', 'max:500'],
        ]);
        $vorlage = Checklistenvorlage::create($daten + ['fuer_neue_boersen' => false]);

        return redirect()->route('admin.checklistenvorlagen.index', ['vorlage' => $vorlage->id])->with('erfolg', 'Vorlage angelegt. Jetzt Einträge hinzufügen.');
    }

    public function vorlageLoeschen(Checklistenvorlage $vorlage): RedirectResponse
    {
        if ($vorlage->fuer_neue_boersen) {
            return back()->with('fehler', 'Die Standard-Checkliste kann nicht gelöscht werden.');
        }
        $vorlage->delete();

        return redirect()->route('admin.checklistenvorlagen.index')->with('erfolg', 'Vorlage gelöscht.');
    }

    /** Die Aufgaben der Vorlage für die aktuelle Börse anlegen. */
    public function anwenden(Checklistenvorlage $vorlage, BoerseKontext $kontext, ChecklisteErzeugen $erzeugen): RedirectResponse
    {
        $boerse = $kontext->getOrFail();
        $anzahl = $erzeugen($boerse, $vorlage->load('eintraege'));

        return redirect()->route('admin.aufgaben.index')->with('erfolg', "{$anzahl} Aufgaben aus „{$vorlage->name}“ für {$boerse->titel} angelegt.");
    }

    public function store(Request $request, Checklistenvorlage $vorlage): RedirectResponse
    {
        $daten = $request->validate([
            'titel' => ['required', 'string', 'max:190'],
            'phase' => ['nullable', 'string', 'max:60'],
            'versatz_tage' => ['required', 'integer', 'min:-365', 'max:365'],
        ]);
        $vorlage->eintraege()->create($daten + ['sortierung' => $vorlage->eintraege()->max('sortierung') + 1]);

        return back()->with('erfolg', $vorlage->fuer_neue_boersen
            ? 'Eintrag hinzugefügt. Er gilt ab der nächsten neu angelegten Börse.'
            : 'Eintrag hinzugefügt.');
    }

    public function destroy(ChecklistenvorlageEintrag $eintrag): RedirectResponse
    {
        $eintrag->delete();

        return back()->with('erfolg', 'Eintrag entfernt.');
    }
}
