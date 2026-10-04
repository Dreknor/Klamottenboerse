<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Domain\Teilnahme\Actions\NummerAendern;
use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Models\Teilnahme;
use App\Support\BoerseKontext;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeilnahmeController extends Controller
{
    public function index(Request $request, BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $status = $request->query('status');
        $suche = trim((string) $request->query('suche'));

        $teilnahmen = $boerse->teilnahmen()
            ->with(['person', 'notizen'])
            ->withCount(['artikel', 'kisten'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when(! $status, fn ($q) => $q->where('status', '!=', TeilnahmeStatus::Abgesagt->value))
            ->when($suche !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nummer', $suche)
                ->orWhereHas('person', fn ($p) => $p->where('nachname', 'like', "%{$suche}%")
                    ->orWhere('vorname', 'like', "%{$suche}%")->orWhere('email', 'like', "%{$suche}%"))))
            ->orderByRaw('nummer is null')->orderBy('nummer')->orderBy('wartelisten_position')
            ->paginate(100)->withQueryString();

        $treffer = $request->filled('person_suche')
            ? Person::query()->where(fn ($q) => $q->where('nachname', 'like', '%'.$request->query('person_suche').'%')
                ->orWhere('vorname', 'like', '%'.$request->query('person_suche').'%')
                ->orWhere('email', 'like', '%'.$request->query('person_suche').'%'))
                ->whereDoesntHave('teilnahmen', fn ($q) => $q->where('boerse_id', $boerse->id)->where('status', '!=', TeilnahmeStatus::Abgesagt->value))
                ->limit(10)->get()
            : collect();

        return view('admin.teilnahmen.index', [
            'boerse' => $boerse,
            'teilnahmen' => $teilnahmen,
            'statusAnzahl' => $boerse->teilnahmen()->where('ist_kinderhaus', false)->selectRaw('status, count(*) as anzahl')->groupBy('status')->pluck('anzahl', 'status'),
            'blockbelegung' => (new Nummernvergabe($boerse))->blockbelegung(),
            'treffer' => $treffer,
        ]);
    }

    /** Manuelle Anmeldung durch das Orga-Team (z. B. nach Mail oder Anruf). */
    public function store(Request $request, BoerseKontext $kontext, Anmelden $anmelden): RedirectResponse
    {
        $boerse = $kontext->getOrFail();

        if ($request->filled('person_id')) {
            $person = Person::findOrFail($request->integer('person_id'));
        } else {
            $daten = $request->validate([
                'vorname' => ['required', 'string', 'max:100'],
                'nachname' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:190', Rule::unique('personen', 'email')],
                'telefon' => ['nullable', 'string', 'max:50'],
                'kinderhaus_bezug' => ['required', Rule::enum(KinderhausBezug::class)],
            ]);
            $person = Person::create($daten);
        }

        $teilnahme = $anmelden($boerse, $person, 'orga', $request->boolean('mail', true));

        return redirect()->route('admin.teilnahmen.index')->with('erfolg', $teilnahme->status === TeilnahmeStatus::Zugeteilt
            ? "{$person->name} hat die Nummer {$teilnahme->nummer} erhalten."
            : "{$person->name} steht auf der Warteliste (Platz {$teilnahme->wartelisten_position}).");
    }

    public function absagen(Teilnahme $teilnahme, Absagen $absagen): RedirectResponse
    {
        try {
            $absagen($teilnahme, 'orga');
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', 'Teilnahme abgesagt. Die Warteliste rückt automatisch nach.');
    }

    public function nummer(Request $request, Teilnahme $teilnahme, NummerAendern $aendern): RedirectResponse
    {
        $request->validate(['nummer' => ['required', 'integer']]);

        try {
            $aendern($teilnahme, $request->integer('nummer'), $request->boolean('mail', true));
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', 'Nummer geändert.');
    }

    public function notiz(Request $request, Teilnahme $teilnahme): RedirectResponse
    {
        $daten = $request->validate(['text' => ['required', 'string', 'max:2000']]);
        $teilnahme->notizen()->create(['text' => $daten['text'], 'autor_id' => $request->user()->id]);

        return back()->with('erfolg', 'Notiz gespeichert.');
    }

    public function nachruecken(BoerseKontext $kontext, WartelisteNachruecken $nachruecken): RedirectResponse
    {
        $anzahl = $nachruecken($kontext->getOrFail());

        return back()->with('erfolg', $anzahl ? "{$anzahl} Angebot(e) an die Warteliste verschickt." : 'Kein freier Platz für die Warteliste.');
    }
}
