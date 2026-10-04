<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Personen\PersonLoeschen;
use App\Http\Controllers\Controller;
use App\Models\Nachricht;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/** Selbstauskunft und Löschung (Art. 15, 17, 20 DSGVO) im Portal. */
class DatenController extends Controller
{
    public function index(Request $request): View
    {
        return view('portal.daten', [
            'person' => $request->user(),
            'offen' => PersonLoeschen::hatOffeneVorgaenge($request->user()),
        ]);
    }

    public function export(Request $request): Response
    {
        $person = $request->user()->load(['teilnahmen.boerse', 'teilnahmen.artikel', 'teilnahmen.abrechnung', 'einteilungen.schicht', 'reservierungen']);

        $daten = [
            'erstellt_am' => now()->toIso8601String(),
            'person' => $person->only(['vorname', 'nachname', 'email', 'telefon', 'created_at']) + [
                'kinderhaus_bezug' => $person->kinderhaus_bezug->label(),
                'info_mails' => $person->info_mails_erlaubt_at ? 'ja, seit '.$person->info_mails_erlaubt_at->format('d.m.Y') : 'nein',
            ],
            'teilnahmen' => $person->teilnahmen->map(fn ($t) => [
                'boerse' => $t->boerse->titel,
                'nummer' => $t->nummer,
                'status' => $t->status->label(),
                'artikel' => $t->artikel->map(fn ($a) => $a->only(['laufnummer', 'beschreibung', 'groesse']) + ['preis' => $a->preis()]),
                'abrechnung' => $t->abrechnung?->only(['umsatz_cent', 'spende_cent', 'auszahlung_cent', 'verkaufte_artikel']),
            ]),
            'schichten' => $person->einteilungen->map(fn ($e) => [
                'bereich' => $e->schicht->bereich,
                'beginn' => $e->schicht->beginn->toIso8601String(),
                'status' => $e->status->value,
            ]),
            'reservierte_nummern' => $person->reservierungen->pluck('nummer'),
            'mails_an_dich' => Nachricht::query()->where('person_id', $person->id)->latest()->get(['betreff', 'status', 'created_at']),
        ];

        return response()->json($daten, 200, [
            'Content-Disposition' => 'attachment; filename="meine-daten-klamottenboerse.json"',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function loeschen(Request $request, PersonLoeschen $loeschen): RedirectResponse
    {
        $request->validate(['bestaetigung' => ['accepted']], ['bestaetigung.accepted' => 'Bitte bestätige, dass deine Daten gelöscht werden sollen.']);
        $person = $request->user();

        if (PersonLoeschen::hatOffeneVorgaenge($person)) {
            $person->forceFill(['loeschung_angefragt_at' => now(), 'info_mails_erlaubt_at' => null])->save();

            return back()->with('erfolg', 'Wir löschen deine Daten automatisch, sobald die laufende Börse abgerechnet ist. Bis dahin bekommst du nur noch Mails zu deiner Teilnahme.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $loeschen($person, 'auf eigenen Wunsch');

        return redirect()->route('start')->with('erfolg', 'Deine Daten wurden gelöscht. Danke, dass du dabei warst!');
    }
}
