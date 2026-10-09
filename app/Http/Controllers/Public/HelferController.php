<?php

namespace App\Http\Controllers\Public;

use App\Domain\Schichten\HelferAbsagen;
use App\Domain\Schichten\HelferEintragen;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Einteilung;
use App\Models\Person;
use App\Models\Schicht;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Öffentliche Helferliste: Schicht wählen, Name und Kontakt eintragen – ohne Login. */
class HelferController extends Controller
{
    public function index(): View
    {
        $boerse = Boerse::query()->offen()->where('verkaufstag', '>=', today())->orderBy('verkaufstag')->first();

        return view('public.helfer', [
            'boerse' => $boerse,
            'schichten' => $boerse
                ? $boerse->schichten()->withCount('zusagen')->get()->groupBy(fn ($s) => $s->beginn->isoFormat('dddd, D. MMMM'))
                : collect(),
        ]);
    }

    public function eintragen(Request $request, Schicht $schicht, HelferEintragen $eintragen): RedirectResponse
    {
        if (filled($request->input('webseite'))) {
            return redirect()->route('helfer.index');
        }

        $daten = $request->validate([
            'vorname' => ['required', 'string', 'max:100'],
            'nachname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'telefon' => ['nullable', 'string', 'max:50'],
        ]);

        $person = Person::query()->where('email', $daten['email'])->first() ?? Person::create($daten);
        if (blank($person->telefon) && filled($daten['telefon'] ?? null)) {
            $person->update(['telefon' => $daten['telefon']]);
        }

        try {
            $eintragen($schicht, $person, 'online', mailSenden: true, ueberbuchenErlaubt: false);
        } catch (DomainException $e) {
            return back()->withInput()->with('fehler', $e->getMessage());
        }

        return redirect()->route('helfer.index')->with('erfolg', "Danke, {$person->vorname}! Du bist eingetragen. Eine Bestätigung kommt per Mail.");
    }

    public function absage(Einteilung $einteilung): View
    {
        return view('public.helfer-absage', ['einteilung' => $einteilung->load('schicht', 'person'), 'erledigt' => false]);
    }

    public function absagen(Einteilung $einteilung, HelferAbsagen $absagen): View
    {
        $absagen($einteilung);

        return view('public.helfer-absage', ['einteilung' => $einteilung->load('schicht', 'person'), 'erledigt' => true]);
    }
}
