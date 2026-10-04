<?php

namespace App\Http\Controllers\Portal;

use App\Enums\TeilnahmeStatus;
use App\Http\Controllers\Controller;
use App\Models\Artikel;
use App\Support\Geld;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Freiwillige Artikelerfassung im Portal – Grundlage für Etiketten mit Barcode. */
class ArtikelController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $teilnahme = PortalController::aktuelleTeilnahme($request);
        abort_unless($teilnahme && $teilnahme->hatNummer() && $teilnahme->status === TeilnahmeStatus::Zugeteilt, 403, 'Artikel können nur bis zur Annahme erfasst werden.');

        $daten = $request->validate([
            'beschreibung' => ['required', 'string', 'max:120'],
            'kategorie_id' => ['nullable', 'exists:kategorien,id'],
            'groesse' => ['nullable', 'string', 'max:20'],
            'preis' => ['required', 'string', 'max:10'],
            'anzahl' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        try {
            $preis = Geld::parse($daten['preis']);
        } catch (\InvalidArgumentException) {
            return back()->withInput()->withErrors(['preis' => 'Bitte einen Preis wie 4,50 eingeben.']);
        }
        if ($preis <= 0 || $preis > 99999) {
            return back()->withInput()->withErrors(['preis' => 'Bitte einen Preis zwischen 0,01 € und 999,99 € eingeben.']);
        }

        $max = $teilnahme->boerse->max_teile;
        $anzahl = (int) ($daten['anzahl'] ?? 1);
        if ($max && $teilnahme->artikel()->count() + $anzahl > $max) {
            return back()->withInput()->with('fehler', "Es sind höchstens {$max} Teile erlaubt.");
        }

        $naechste = (int) Artikel::withTrashed()->where('teilnahme_id', $teilnahme->id)->max('laufnummer');
        for ($i = 1; $i <= $anzahl; $i++) {
            $teilnahme->artikel()->create([
                'laufnummer' => $naechste + $i,
                'beschreibung' => $daten['beschreibung'],
                'kategorie_id' => $daten['kategorie_id'] ?? null,
                'groesse' => $daten['groesse'] ?? null,
                'preis_cent' => $preis,
            ]);
        }

        return back()->withInput($request->only('kategorie_id', 'groesse'))
            ->with('erfolg', $anzahl > 1 ? "{$anzahl} Artikel hinzugefügt." : 'Artikel hinzugefügt.');
    }

    public function destroy(Request $request, Artikel $artikel): RedirectResponse
    {
        $teilnahme = PortalController::aktuelleTeilnahme($request);
        abort_unless($teilnahme && $artikel->teilnahme_id === $teilnahme->id && $teilnahme->status === TeilnahmeStatus::Zugeteilt, 403);

        $artikel->delete();

        return back()->with('erfolg', 'Artikel entfernt. Seine Nummer wird nicht neu vergeben, damit gedruckte Etiketten eindeutig bleiben.');
    }
}
