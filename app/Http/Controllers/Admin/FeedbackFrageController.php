<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackFrage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Fragen der Feedback-Umfrage pflegen – gelten für alle künftigen Antworten, unabhängig von der Börse. */
class FeedbackFrageController extends Controller
{
    public function index(): View
    {
        return view('admin.feedback.fragen', [
            'fragen' => FeedbackFrage::query()->sortiert()->withCount('antworten')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FeedbackFrage::create($this->validiert($request) + [
            'sortierung' => (int) FeedbackFrage::query()->max('sortierung') + 10,
            'aktiv' => true,
        ]);

        return back()->with('erfolg', 'Frage angelegt. Sie erscheint ab sofort in der Umfrage.');
    }

    public function update(Request $request, FeedbackFrage $frage): RedirectResponse
    {
        $daten = $this->validiert($request) + [
            'sortierung' => $request->integer('sortierung'),
            'aktiv' => $request->boolean('aktiv'),
        ];
        if ($daten['typ'] !== $frage->typ && $frage->antworten()->exists()) {
            return back()->with('fehler', 'Zu dieser Frage gibt es schon Antworten – der Typ lässt sich nicht mehr ändern. Lege dafür eine neue Frage an.');
        }
        $frage->update($daten);

        return back()->with('erfolg', 'Frage gespeichert.');
    }

    public function destroy(FeedbackFrage $frage): RedirectResponse
    {
        if ($frage->antworten()->exists()) {
            return back()->with('fehler', 'Zu dieser Frage gibt es schon Antworten. Bitte statt Löschen ausblenden – dann bleibt die Auswertung früherer Börsen erhalten.');
        }
        $frage->delete();

        return back()->with('erfolg', 'Frage gelöscht.');
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request): array
    {
        $daten = $request->validate([
            'text' => ['required', 'string', 'max:255'],
            'typ' => ['required', Rule::in(array_keys(FeedbackFrage::TYPEN))],
            'optionen' => ['nullable', 'string', 'max:2000', 'required_if:typ,auswahl'],
            'rolle' => ['nullable', Rule::in(['verkaeufer', 'helfer'])],
        ], ['optionen.required_if' => 'Für eine Auswahl-Frage bitte die Antwortmöglichkeiten angeben (eine pro Zeile).']);

        $optionen = collect(preg_split('/\R/', $daten['optionen'] ?? ''))->map(fn ($o) => trim($o))->filter()->unique()->values();
        if ($daten['typ'] === 'auswahl' && $optionen->count() < 2) {
            throw ValidationException::withMessages(['optionen' => 'Eine Auswahl braucht mindestens zwei Antwortmöglichkeiten.']);
        }

        return [
            'text' => $daten['text'],
            'typ' => $daten['typ'],
            'optionen' => $daten['typ'] === 'auswahl' ? $optionen->all() : null,
            'rolle' => $daten['rolle'] ?? null,
            'pflicht' => $request->boolean('pflicht'),
        ];
    }
}
