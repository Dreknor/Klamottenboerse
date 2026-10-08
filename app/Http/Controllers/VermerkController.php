<?php

namespace App\Http\Controllers;

use App\Domain\Reputation\Reputation;
use App\Models\Boerse;
use App\Models\Person;
use App\Models\Vermerk;
use App\Models\VermerkArt;
use App\Support\BoerseKontext;
use App\Support\Einstellungen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Vermerke zur Reputation der Verkäufer.
 * Schnellerfassung (Kasse, Annahme, Rückpacken, Ausgabe) über die Verkäufernummer der aktuellen Börse;
 * im Backend außerdem Übersicht, Erfassung je Person, Arten und Schwellen.
 */
class VermerkController extends Controller
{
    /** Schnellerfassung im Tablet-Layout – für alle Rollen an Kasse und Annahme. */
    public function schnell(Request $request): View
    {
        $quelle = array_key_exists((string) $request->query('quelle'), Vermerk::QUELLEN) ? $request->query('quelle') : 'annahme';
        $zurueck = $this->sichereAdresse($request->query('zurueck')) ?? $this->sichereAdresse(url()->previous()) ?? route('tablet.index');

        return view('vermerke.schnell', [
            'boerse' => Boerse::aktuelle(),
            'arten' => VermerkArt::query()->aktiv()->get(),
            'quelle' => $quelle,
            'nummer' => $request->query('nummer'),
            'zurueck' => $zurueck,
        ]);
    }

    public function schnellSpeichern(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'nummer' => ['required', 'integer'],
            'vermerk_art_id' => ['required', Rule::exists('vermerk_arten', 'id')->where('aktiv', true)],
            'bemerkung' => ['nullable', 'string', 'max:1000'],
            'quelle' => ['required', Rule::in(array_keys(Vermerk::QUELLEN))],
            'zurueck' => ['nullable', 'string'],
        ], [
            'nummer.required' => 'Bitte die Verkäufernummer eingeben.',
            'vermerk_art_id.required' => 'Bitte auswählen, was vorgefallen ist.',
        ]);

        $boerse = Boerse::aktuelle();
        $teilnahme = $boerse?->teilnahmen()->mitNummer()->where('nummer', $daten['nummer'])->with('person')->first();
        if (! $teilnahme?->person) {
            return back()->withInput()->withErrors(['nummer' => "Zur Nummer {$daten['nummer']} gibt es in der aktuellen Börse keinen Verkäufer."]);
        }

        $this->anlegen($teilnahme->person, $boerse, VermerkArt::findOrFail($daten['vermerk_art_id']), $daten['bemerkung'] ?? null, $daten['quelle'], $request->user());

        $zurueck = $this->sichereAdresse($daten['zurueck'] ?? null) ?? route('tablet.index');

        return redirect($zurueck)->with('erfolg', "Vermerk für Nr. {$teilnahme->nummer} ({$teilnahme->person->vorname} ".Str::substr($teilnahme->person->nachname, 0, 1).'.) gespeichert.');
    }

    /** Backend: letzte Vermerke, Verkäufer mit schlechter Reputation, Arten und Schwellen. */
    public function index(): View
    {
        return view('admin.vermerke.index', [
            'vermerke' => Vermerk::query()->with(['person', 'erfasser', 'boerse'])->latest()->paginate(30),
            'auffaellig' => Person::query()->mitReputation()
                ->where(fn ($q) => $q->whereNotNull('nummernvergabe')->orWhereHas('vermerke', fn ($v) => $v->wirksam()))
                ->get()
                ->filter(fn (Person $p) => Reputation::status($p) !== Reputation::GUT || $p->nummernvergabe)
                ->sortByDesc('reputation_punkte')->values(),
            'arten' => VermerkArt::query()->orderBy('sortierung')->orderBy('name')->get(),
            'schwellen' => [
                'warnung' => Reputation::warnungAb(),
                'sperre' => Reputation::sperreAb(),
                'monate' => Reputation::zeitraumMonate(),
            ],
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.vermerke.create', [
            'person' => $request->filled('person') ? Person::query()->find($request->integer('person')) : null,
            'arten' => VermerkArt::query()->aktiv()->get(),
        ]);
    }

    public function store(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $request->validate([
            'person_id' => ['required', 'exists:personen,id'],
            'vermerk_art_id' => ['required', Rule::exists('vermerk_arten', 'id')->where('aktiv', true)],
            'bemerkung' => ['nullable', 'string', 'max:1000'],
        ], ['person_id.required' => 'Bitte eine Person auswählen.', 'vermerk_art_id.required' => 'Bitte auswählen, was vorgefallen ist.']);

        $person = Person::findOrFail($daten['person_id']);
        $this->anlegen($person, $kontext->get(), VermerkArt::findOrFail($daten['vermerk_art_id']), $daten['bemerkung'] ?? null, 'backend', $request->user());

        return redirect()->route('admin.personen.show', $person)->withFragment('vermerke')->with('erfolg', 'Vermerk gespeichert.');
    }

    public function destroy(Vermerk $vermerk): RedirectResponse
    {
        $vermerk->delete();
        activity()->performedOn($vermerk->person)->withProperties(['art' => $vermerk->art, 'punkte' => $vermerk->punkte])->log('Vermerk gelöscht');

        return back()->with('erfolg', 'Vermerk gelöscht.');
    }

    /** Festlegung je Person: nach Punkten / immer händisch / trotz Punkten automatisch. */
    public function vergabe(Request $request, Person $person): RedirectResponse
    {
        $daten = $request->validate(['nummernvergabe' => ['nullable', Rule::in(['haendisch', 'frei'])]]);
        $person->update(['nummernvergabe' => $daten['nummernvergabe'] ?? null]);
        activity()->performedOn($person)->withProperties($daten)->log('Nummernvergabe festgelegt');

        return back()->with('erfolg', match ($person->nummernvergabe) {
            'haendisch' => 'Diese Person bekommt Nummern nur noch händisch.',
            'frei' => 'Diese Person bekommt trotz Vermerken wieder automatisch eine Nummer.',
            default => 'Die Nummernvergabe richtet sich wieder nach den Punkten.',
        });
    }

    public function artSpeichern(Request $request, ?VermerkArt $art = null): RedirectResponse
    {
        $daten = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'punkte' => ['required', 'integer', 'min:0', 'max:20'],
            'sortierung' => ['nullable', 'integer'],
        ]);

        if ($art?->exists) {
            $art->update($daten + ['aktiv' => $request->boolean('aktiv')]);
        } else {
            VermerkArt::create($daten + ['sortierung' => (int) VermerkArt::query()->max('sortierung') + 10, 'aktiv' => true]);
        }

        return back()->with('erfolg', 'Vermerk-Art gespeichert. Bereits erfasste Vermerke behalten ihre Punkte.');
    }

    public function schwellen(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'reputation_warnung_ab' => ['required', 'integer', 'min:1', 'max:100'],
            'reputation_sperre_ab' => ['required', 'integer', 'min:1', 'max:100', 'gte:reputation_warnung_ab'],
            'reputation_zeitraum_monate' => ['required', 'integer', 'min:1', 'max:120'],
        ], ['reputation_sperre_ab.gte' => 'Die Sperre sollte nicht unter der Warnschwelle liegen.']);

        foreach ($daten as $schluessel => $wert) {
            Einstellungen::set($schluessel, (int) $wert);
        }

        return back()->with('erfolg', 'Schwellen gespeichert.');
    }

    private function anlegen(Person $person, ?Boerse $boerse, VermerkArt $art, ?string $bemerkung, string $quelle, Person $erfasser): Vermerk
    {
        $vorher = Reputation::status($person);

        $vermerk = $person->vermerke()->create([
            'boerse_id' => $boerse?->id,
            'vermerk_art_id' => $art->id,
            'art' => $art->name,
            'punkte' => $art->punkte,
            'bemerkung' => $bemerkung,
            'quelle' => $quelle,
            'erfasst_von' => $erfasser->id,
        ]);

        if ($vorher !== Reputation::GESPERRT && Reputation::status($person->refresh()) === Reputation::GESPERRT) {
            activity()->performedOn($person)->log('Reputation: ab jetzt nur noch händische Nummernvergabe');
        }

        return $vermerk;
    }

    /** Nur Adressen dieser Seite als Rücksprung zulassen. */
    private function sichereAdresse(?string $adresse): ?string
    {
        if (! $adresse || ! str_starts_with($adresse, url('/')) || str_contains($adresse, '/vermerk')) {
            return null;
        }

        return $adresse;
    }
}
