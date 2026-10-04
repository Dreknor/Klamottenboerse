<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\Platzhalter;
use App\Domain\Kommunikation\Rundnachricht;
use App\Enums\Zielgruppe;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Support\BoerseKontext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** „Nachricht schreiben“: an einzelne Personen oder Gruppen (Verkäufer, Helfer, Team …). */
class RundnachrichtController extends Controller
{
    public function create(Request $request, BoerseKontext $kontext): View
    {
        $vorauswahl = Person::query()->whereIn('id', (array) $request->query('person', []))
            ->get(['id', 'vorname', 'nachname', 'email'])
            ->map(fn (Person $p) => ['id' => $p->id, 'name' => $p->nachname.', '.$p->vorname, 'email' => $p->email])->values();

        return view('admin.rundnachricht.create', [
            'boerse' => $kontext->get(),
            'gruppen' => Zielgruppe::cases(),
            'ohneBoerse' => Rundnachricht::OHNE_BOERSE,
            'vorauswahl' => $vorauswahl,
            'platzhalter' => array_intersect_key(Platzhalter::BESCHREIBUNG, array_flip(['vorname', 'nachname', 'nummer', 'datum', 'portal_link', 'helfer_link'])),
        ]);
    }

    public function vorschau(Request $request, Rundnachricht $rundnachricht, BoerseKontext $kontext): JsonResponse
    {
        $personen = $rundnachricht->empfaenger((array) $request->input('gruppen', []), array_map('intval', (array) $request->input('personen', [])), $kontext->get());

        return response()->json($rundnachricht->vorschau($personen));
    }

    public function store(Request $request, Rundnachricht $rundnachricht, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $request->validate([
            'gruppen' => ['array'],
            'gruppen.*' => ['string'],
            'personen' => ['array'],
            'personen.*' => ['integer', 'exists:personen,id'],
            'betreff' => ['required', 'string', 'max:190'],
            'text' => ['required', 'string', 'max:20000'],
            'mail' => ['nullable', 'boolean'],
            'push' => ['nullable', 'boolean'],
        ]);

        if (! $request->boolean('mail') && ! $request->boolean('push')) {
            return back()->withInput()->with('fehler', 'Bitte E-Mail und/oder Push auswählen.');
        }

        $boerse = $kontext->get();
        $personen = $rundnachricht->empfaenger($daten['gruppen'] ?? [], $daten['personen'] ?? [], $boerse);
        if ($personen->isEmpty()) {
            return back()->withInput()->with('fehler', 'Keine Empfänger ausgewählt.');
        }

        $ergebnis = $rundnachricht->senden($personen, $daten['betreff'], $daten['text'], $boerse, $request->boolean('mail'), $request->boolean('push'));
        activity()->withProperties(['betreff' => $daten['betreff'], 'gruppen' => $daten['gruppen'] ?? [], 'empfaenger' => $personen->count()] + $ergebnis)
            ->log('Rundnachricht verschickt');

        return redirect()->route('admin.postausgang.index')->with('erfolg',
            "Nachricht an {$personen->count()} Personen: {$ergebnis['mail']} E-Mails eingeplant (Versand im Rahmen des Stundenlimits), {$ergebnis['push']} Push-Nachrichten.");
    }
}
