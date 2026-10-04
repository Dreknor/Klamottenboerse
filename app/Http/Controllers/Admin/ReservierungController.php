<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
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
            'reservierungen' => Nummernreservierung::query()->with(['person', 'boerse', 'freigaben'])->orderBy('nummer')->get(),
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
        activity()->performedOn($reservierung->person)->withProperties(['nummer' => $reservierung->nummer])->log('Reservierung aufgehoben');
        $reservierung->delete();
        $this->nachruecken();

        return back()->with('erfolg', "Reservierung der Nummer {$reservierung->nummer} aufgehoben. Die Nummer ist wieder frei.");
    }

    /** Nur für die aktuelle Börse freigeben – die Reservierung bleibt für spätere Börsen bestehen. */
    public function freigeben(Nummernreservierung $reservierung, BoerseKontext $kontext): RedirectResponse
    {
        $boerse = $kontext->getOrFail();
        $belegt = $boerse->teilnahmen()->mitNummer()->where('person_id', $reservierung->person_id)->where('nummer', $reservierung->nummer)->exists();
        if ($belegt) {
            return back()->with('fehler', 'Die Person nutzt diese Nummer bei dieser Börse bereits. Zum Freigeben bitte zuerst ihre Teilnahme absagen.');
        }

        $reservierung->freigebenFuer($boerse, 'vom Orga-Team freigegeben');
        $this->nachruecken();

        return back()->with('erfolg', "Nummer {$reservierung->nummer} ist für {$boerse->titel} frei.");
    }

    public function freigabeZuruecknehmen(Nummernreservierung $reservierung, BoerseKontext $kontext): RedirectResponse
    {
        $boerse = $kontext->getOrFail();
        if ($boerse->teilnahmen()->mitNummer()->where('nummer', $reservierung->nummer)->where('person_id', '!=', $reservierung->person_id)->exists()) {
            return back()->with('fehler', "Die Nummer {$reservierung->nummer} ist bei dieser Börse inzwischen an jemand anderen vergeben.");
        }
        $reservierung->freigaben()->detach($boerse->id);

        return back()->with('erfolg', "Nummer {$reservierung->nummer} ist wieder reserviert.");
    }

    private function nachruecken(): void
    {
        if ($boerse = app(BoerseKontext::class)->get()) {
            app(WartelisteNachruecken::class)($boerse);
        }
    }
}
