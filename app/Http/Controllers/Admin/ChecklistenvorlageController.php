<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checklistenvorlage;
use App\Models\ChecklistenvorlageEintrag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Die Vorlage, aus der jede neue Börse ihre Checkliste bekommt. */
class ChecklistenvorlageController extends Controller
{
    public function index(): View
    {
        $vorlage = Checklistenvorlage::query()->with('eintraege')->firstOrCreate(
            ['fuer_neue_boersen' => true],
            ['name' => 'Standard-Checkliste je Börse'],
        );

        return view('admin.aufgaben.vorlage', ['vorlage' => $vorlage]);
    }

    public function store(Request $request, Checklistenvorlage $vorlage): RedirectResponse
    {
        $daten = $request->validate([
            'titel' => ['required', 'string', 'max:190'],
            'phase' => ['nullable', 'string', 'max:60'],
            'versatz_tage' => ['required', 'integer', 'min:-365', 'max:365'],
        ]);
        $vorlage->eintraege()->create($daten + ['sortierung' => $vorlage->eintraege()->max('sortierung') + 1]);

        return back()->with('erfolg', 'Eintrag hinzugefügt. Er gilt ab der nächsten neu angelegten Börse.');
    }

    public function destroy(ChecklistenvorlageEintrag $eintrag): RedirectResponse
    {
        $eintrag->delete();

        return back()->with('erfolg', 'Eintrag entfernt.');
    }
}
