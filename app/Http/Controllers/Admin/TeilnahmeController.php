<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\AnfrageEntscheiden;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Domain\Teilnahme\Actions\NummerAendern;
use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
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
            ->with(['person' => fn ($q) => $q->mitReputation(), 'notizen'])
            ->withCount(['artikel', 'kisten'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when(! $status, fn ($q) => $q->where('status', '!=', TeilnahmeStatus::Abgesagt->value))
            ->when($suche !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('nummer', $suche)
                ->orWhereHas('person', fn ($p) => $p->where('nachname', 'like', "%{$suche}%")
                    ->orWhere('vorname', 'like', "%{$suche}%")->orWhere('email', 'like', "%{$suche}%"))))
            ->orderByRaw('nummer is null')->orderBy('nummer')->orderBy('wartelisten_position')
            ->paginate(100)->withQueryString();

        return view('admin.teilnahmen.index', [
            'boerse' => $boerse,
            'teilnahmen' => $teilnahmen,
            'statusAnzahl' => $boerse->teilnahmen()->where('ist_kinderhaus', false)->selectRaw('status, count(*) as anzahl')->groupBy('status')->pluck('anzahl', 'status'),
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

        return redirect()->route('admin.teilnahmen.index')->with('erfolg', match ($teilnahme->status) {
            TeilnahmeStatus::Zugeteilt => "{$person->name} hat die Nummer {$teilnahme->nummer} erhalten.",
            TeilnahmeStatus::Warteliste => "{$person->name} steht auf der Warteliste (Platz {$teilnahme->wartelisten_position}).",
            default => "{$person->name}: {$teilnahme->status->label()}.",
        });
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

    public function anfrageVergeben(Teilnahme $teilnahme, AnfrageEntscheiden $entscheiden): RedirectResponse
    {
        try {
            $teilnahme = $entscheiden->vergeben($teilnahme);
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', "{$teilnahme->person?->name} hat die Nummer {$teilnahme->nummer} erhalten.");
    }

    public function anfrageAblehnen(Teilnahme $teilnahme, AnfrageEntscheiden $entscheiden): RedirectResponse
    {
        try {
            $entscheiden->ablehnen($teilnahme);
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', 'Anfrage abgelehnt. Die Person bekommt eine Mail.');
    }

    public function nachruecken(BoerseKontext $kontext, WartelisteNachruecken $nachruecken): RedirectResponse
    {
        $anzahl = $nachruecken($kontext->getOrFail());

        return back()->with('erfolg', $anzahl ? "{$anzahl} Angebot(e) an die Warteliste verschickt." : 'Kein freier Platz für die Warteliste.');
    }
}
