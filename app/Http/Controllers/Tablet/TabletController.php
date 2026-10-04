<?php

namespace App\Http\Controllers\Tablet;

use App\Enums\TeilnahmeStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Teilnahme;
use App\Support\Geld;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Vereinfachte Ansichten für Helfer vor Ort (Annahme, Rückpacken, Ausgabe).
 * Gezeigt werden nur Nummer und Name – keine Kontaktdaten.
 */
class TabletController extends Controller
{
    public function index(): View
    {
        return view('tablet.index', ['boerse' => $this->boerse()]);
    }

    public function annahme(Request $request): View
    {
        $boerse = $this->boerse();
        $verkaeufer = $boerse->teilnahmen()->mitNummer();

        return view('tablet.annahme', [
            'boerse' => $boerse,
            'treffer' => $this->suchen($boerse, (string) $request->query('suche')),
            'angeliefert' => (clone $verkaeufer)->whereNotNull('angeliefert_at')->count(),
            'gesamt' => (clone $verkaeufer)->count(),
            'offen' => (clone $verkaeufer)->whereNull('angeliefert_at')->where('ist_kinderhaus', false)->orderBy('nummer')->with('person:id,vorname,nachname')->get(),
        ]);
    }

    public function annehmen(Request $request, Teilnahme $teilnahme): RedirectResponse
    {
        $daten = $request->validate([
            'kisten' => ['required', 'integer', 'min:1', 'max:10'],
            'bemerkung' => ['nullable', 'string', 'max:500'],
        ]);

        for ($nr = $teilnahme->kisten()->count() + 1; $teilnahme->kisten()->count() < $daten['kisten']; $nr++) {
            $teilnahme->kisten()->create([
                'kistennummer' => $nr,
                'angenommen_at' => now(),
                'angenommen_von' => $request->user()->id,
                'bemerkung' => $daten['bemerkung'] ?? null,
            ]);
        }
        if (filled($daten['bemerkung'] ?? null)) {
            $teilnahme->kisten()->update(['bemerkung' => $daten['bemerkung']]);
        }

        $teilnahme->update([
            'angeliefert_at' => $teilnahme->angeliefert_at ?? now(),
            'status' => $teilnahme->status === TeilnahmeStatus::Zugeteilt ? TeilnahmeStatus::Angeliefert : $teilnahme->status,
        ]);

        return redirect()->route('tablet.annahme')->with('erfolg', "Nummer {$teilnahme->nummer}: {$daten['kisten']} Kiste(n) angenommen.");
    }

    /** Rückpack-Liste: Was wurde verkauft, was gehört zurück in die Kiste? */
    public function rueckpacken(Request $request): View
    {
        $boerse = $this->boerse();
        $teilnahme = $request->filled('nummer')
            ? $boerse->teilnahmen()->mitNummer()->where('nummer', $request->integer('nummer'))->with('person:id,vorname,nachname', 'artikel')->first()
            : null;

        $verkauft = $teilnahme ? $teilnahme->gueltigePositionen()->orderBy('artikelnummer')->pluck('artikelnummer') : collect();

        return view('tablet.rueckpacken', [
            'boerse' => $boerse,
            'teilnahme' => $teilnahme,
            'verkauft' => $verkauft,
            'zurueck' => $teilnahme ? $teilnahme->artikel->reject(fn ($a) => $verkauft->contains($a->laufnummer)) : collect(),
        ]);
    }

    public function ausgabe(Request $request): View
    {
        $boerse = $this->boerse();
        $treffer = $this->suchen($boerse, (string) $request->query('suche'))->load('abrechnung', 'kisten');

        return view('tablet.ausgabe', [
            'boerse' => $boerse,
            'treffer' => $treffer,
            'stueckelung' => $treffer->mapWithKeys(fn ($t) => [$t->id => $t->abrechnung ? Geld::stueckelung($t->abrechnung->auszahlung_cent) : []]),
            'offen' => $boerse->teilnahmen()->where('status', TeilnahmeStatus::Abgerechnet)->where('ist_kinderhaus', false)->count(),
            'ausgezahlt' => $boerse->teilnahmen()->where('status', TeilnahmeStatus::Ausgezahlt)->count(),
        ]);
    }

    public function auszahlen(Request $request, Teilnahme $teilnahme): RedirectResponse
    {
        abort_unless($teilnahme->abrechnung, 422, 'Für diese Nummer gibt es noch keine Abrechnung.');

        $teilnahme->abrechnung->update(['ausgezahlt_at' => now(), 'ausgezahlt_von' => $request->user()->id]);
        $teilnahme->kisten()->whereNull('ausgegeben_at')->update(['ausgegeben_at' => now(), 'ausgegeben_von' => $request->user()->id]);
        $teilnahme->update(['status' => TeilnahmeStatus::Ausgezahlt]);
        activity()->performedOn($teilnahme)->causedBy($request->user())->log('Erlös ausgezahlt und Kisten ausgegeben');

        return redirect()->route('tablet.ausgabe')->with('erfolg', "Nummer {$teilnahme->nummer} ausgegeben.");
    }

    private function boerse(): Boerse
    {
        return Boerse::aktuelle() ?? abort(404, 'Es gibt keine aktuelle Börse.');
    }

    /** Suche nach Nummer (auch per QR-Code vom Kistenzettel) oder Name. */
    private function suchen(Boerse $boerse, string $suche): Collection
    {
        $suche = trim($suche);
        if ($suche === '') {
            return collect();
        }

        return $boerse->teilnahmen()->mitNummer()
            ->with('person:id,vorname,nachname')
            ->where(fn ($q) => ctype_digit($suche)
                ? $q->where('nummer', (int) $suche)
                : $q->whereHas('person', fn ($p) => $p->where('nachname', 'like', "%{$suche}%")->orWhere('vorname', 'like', "%{$suche}%")))
            ->orderBy('nummer')->limit(10)->get();
    }
}
