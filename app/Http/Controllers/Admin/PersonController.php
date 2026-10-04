<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Personen\InaktiveBereinigen;
use App\Domain\Personen\PersonLoeschen;
use App\Enums\KinderhausBezug;
use App\Http\Controllers\Controller;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Posteingang;
use App\Models\Teilnahme;
use Database\Seeders\GrunddatenSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class PersonController extends Controller
{
    public function index(Request $request): View
    {
        $suche = trim((string) $request->query('suche'));

        return view('admin.personen.index', [
            'personen' => Person::query()
                ->with('roles')
                ->withCount('teilnahmen')
                ->when($suche !== '', fn ($q) => $q->where(fn ($w) => $w->where('nachname', 'like', "%{$suche}%")
                    ->orWhere('vorname', 'like', "%{$suche}%")->orWhere('email', 'like', "%{$suche}%")->orWhere('telefon', 'like', "%{$suche}%")))
                ->when($request->query('rolle'), fn ($q, $rolle) => $q->role($rolle))
                ->orderBy('nachname')->orderBy('vorname')
                ->paginate(50)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.personen.form', ['person' => new Person(['kinderhaus_bezug' => KinderhausBezug::Keiner])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $person = Person::create($this->validiert($request));
        $this->rollenUndPasswort($request, $person);

        return redirect()->route('admin.personen.show', $person)->with('erfolg', 'Person angelegt.');
    }

    public function show(Person $person): View
    {
        return view('admin.personen.show', [
            'person' => $person->load(['teilnahmen.boerse', 'teilnahmen.abrechnung', 'teilnahmen.notizen', 'einteilungen.schicht.boerse', 'reservierungen.boerse', 'notizen.autor', 'roles']),
            'nachrichten' => Nachricht::query()->where('person_id', $person->id)->latest()->limit(20)->get(),
            'posteingang' => Posteingang::query()->where('person_id', $person->id)->latest('empfangen_at')->limit(20)->get(),
            'aktivitaeten' => Activity::query()
                ->where(fn ($q) => $q
                    ->where(fn ($p) => $p->where('subject_type', $person->getMorphClass())->where('subject_id', $person->id))
                    ->orWhere(fn ($t) => $t->where('subject_type', (new Teilnahme)->getMorphClass())->whereIn('subject_id', $person->teilnahmen->pluck('id'))))
                ->latest()->limit(20)->get(),
        ]);
    }

    public function edit(Person $person): View
    {
        return view('admin.personen.form', ['person' => $person]);
    }

    public function update(Request $request, Person $person): RedirectResponse
    {
        $person->update($this->validiert($request, $person));
        $this->rollenUndPasswort($request, $person);

        return redirect()->route('admin.personen.show', $person)->with('erfolg', 'Gespeichert.');
    }

    public function notiz(Request $request, Person $person): RedirectResponse
    {
        $daten = $request->validate(['text' => ['required', 'string', 'max:5000']]);
        $person->notizen()->create(['text' => $daten['text'], 'autor_id' => $request->user()->id]);

        return back()->with('erfolg', 'Notiz gespeichert.');
    }

    public function loginLink(Person $person): RedirectResponse
    {
        $nachricht = Postausgang::einplanen($person, 'login_link');
        if (! $nachricht) {
            return back()->with('fehler', 'Diese Person hat keine E-Mail-Adresse.');
        }
        Postausgang::senden($nachricht);

        return back()->with('erfolg', 'Link zum Portal wurde verschickt.');
    }

    public function destroy(Request $request, Person $person, PersonLoeschen $loeschen): RedirectResponse
    {
        $request->validate(['bestaetigung' => ['required', 'in:'.$person->nachname]], [
            'bestaetigung.in' => 'Zur Sicherheit bitte den Nachnamen genau so eintippen.',
        ]);

        if ($person->is($request->user())) {
            return back()->with('fehler', 'Du kannst dich nicht selbst löschen.');
        }
        if (PersonLoeschen::hatOffeneVorgaenge($person)) {
            return back()->with('fehler', 'Diese Person nimmt gerade an einer Börse teil. Bitte erst die Teilnahme absagen oder nach der Abrechnung löschen.');
        }

        $loeschen($person, 'vom Orga-Team gelöscht');

        return redirect()->route('admin.personen.index')->with('erfolg', 'Person gelöscht. Verkaufszahlen bleiben ohne Namen erhalten.');
    }

    /** Übersicht für das Löschkonzept: wer gilt als inaktiv, wer wurde angeschrieben. */
    public function inaktive(InaktiveBereinigen $bereinigen): View
    {
        return view('admin.personen.inaktive', [
            'kandidaten' => $bereinigen->inaktive()->whereNull('inaktiv_angeschrieben_at')->orderBy('nachname')->get(),
            'angeschrieben' => $bereinigen->inaktive()->whereNotNull('inaktiv_angeschrieben_at')->orderBy('inaktiv_angeschrieben_at')->get(),
            'beantragt' => Person::query()->whereNotNull('loeschung_angefragt_at')->orderBy('loeschung_angefragt_at')->get(),
            'frist' => InaktiveBereinigen::FRIST_TAGE,
            'monate' => InaktiveBereinigen::MONATE,
        ]);
    }

    /** @return array<string, mixed> */
    private function validiert(Request $request, ?Person $person = null): array
    {
        $daten = $request->validate([
            'vorname' => ['required', 'string', 'max:100'],
            'nachname' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:190', Rule::unique('personen', 'email')->ignore($person)],
            'telefon' => ['nullable', 'string', 'max:50'],
            'kinderhaus_bezug' => ['required', Rule::enum(KinderhausBezug::class)],
            'info_mails' => ['nullable', 'boolean'],
        ]);

        $daten['info_mails_erlaubt_at'] = $request->boolean('info_mails') ? ($person?->info_mails_erlaubt_at ?? now()) : null;
        unset($daten['info_mails']);

        return $daten;
    }

    private function rollenUndPasswort(Request $request, Person $person): void
    {
        if (! $request->user()->hasRole('admin')) {
            return;
        }

        $request->validate([
            'rollen' => ['array'],
            'rollen.*' => [Rule::in(array_keys(GrunddatenSeeder::ROLLEN))],
            'password' => ['nullable', Password::min(10)],
        ]);

        $person->syncRoles($request->input('rollen', []));

        if ($request->filled('password')) {
            $person->update(['password' => $request->input('password')]);
        }
    }
}
