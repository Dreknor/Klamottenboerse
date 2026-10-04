<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fehler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Fehlerprotokoll: was ist auf dem Server schiefgegangen? */
class FehlerController extends Controller
{
    public function index(Request $request): View
    {
        $ansicht = $request->query('ansicht', 'offen');

        return view('admin.system.fehler', [
            'ansicht' => $ansicht,
            'fehler' => Fehler::query()
                ->when($ansicht === 'offen', fn ($q) => $q->whereNull('erledigt_at'))
                ->when($ansicht === 'erledigt', fn ($q) => $q->whereNotNull('erledigt_at'))
                ->when($request->query('stufe'), fn ($q, $stufe) => $q->where('stufe', $stufe))
                ->latest('zuletzt_at')
                ->paginate(50)->withQueryString(),
        ]);
    }

    public function show(Fehler $fehler): View
    {
        return view('admin.system.fehler-detail', ['fehler' => $fehler->load('person')]);
    }

    public function erledigt(Fehler $fehler): RedirectResponse
    {
        $fehler->update(['erledigt_at' => $fehler->erledigt_at ? null : now()]);

        return back()->with('erfolg', $fehler->erledigt_at ? 'Als erledigt markiert. Tritt der Fehler erneut auf, erscheint er wieder.' : 'Wieder offen.');
    }

    public function leeren(Request $request): RedirectResponse
    {
        $anzahl = $request->input('was') === 'alle'
            ? Fehler::query()->delete()
            : Fehler::query()->whereNotNull('erledigt_at')->delete();

        return back()->with('erfolg', "{$anzahl} Einträge gelöscht.");
    }
}
