<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Abrechnung\Auszahlungsplan;
use App\Domain\Kommunikation\Postausgang;
use App\Http\Controllers\Controller;
use App\Models\Abrechnung;
use App\Models\Boerse;
use App\Models\Nachricht;
use App\Models\SonstigeEinnahme;
use App\Support\BoerseKontext;
use App\Support\Geld;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AbrechnungController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $abrechnungen = Abrechnung::query()
            ->whereHas('teilnahme', fn ($q) => $q->where('boerse_id', $boerse->id))
            ->with('teilnahme.person')
            ->get()
            ->sortBy(fn ($a) => $a->teilnahme->nummer);

        $kinderhaus = $abrechnungen->first(fn ($a) => $a->teilnahme->ist_kinderhaus);
        $verkaeufer = $abrechnungen->reject(fn ($a) => $a->teilnahme->ist_kinderhaus);
        $einnahmen = $boerse->sonstigeEinnahmen()->latest()->get();

        return view('admin.abrechnung.index', [
            'boerse' => $boerse,
            'abrechnungen' => $abrechnungen,
            'summen' => self::summen($boerse, $verkaeufer, $kinderhaus, $einnahmen->sum('betrag_cent')),
            'einnahmen' => $einnahmen,
            'bonsGesamtCent' => (int) $boerse->bons()->whereNull('storniert_at')->sum('summe_cent'),
            'zuletztBerechnet' => $abrechnungen->max('berechnet_at'),
        ]);
    }

    public function berechnen(BoerseKontext $kontext, AbrechnungBerechnen $berechnen): RedirectResponse
    {
        $anzahl = $berechnen($kontext->getOrFail());

        return back()->with('erfolg', "Abrechnung für {$anzahl} Nummern berechnet.");
    }

    /** Ergebnis für Verkäufer im Portal freigeben und per Mail verschicken. */
    public function freigeben(BoerseKontext $kontext): RedirectResponse
    {
        $boerse = $kontext->getOrFail();
        $boerse->update(['ergebnis_freigegeben' => true]);

        $bereits = Nachricht::query()->where('boerse_id', $boerse->id)->where('typ', 'ergebnis')->pluck('person_id')->flip();
        $mails = 0;

        Abrechnung::query()->whereHas('teilnahme', fn ($q) => $q->where('boerse_id', $boerse->id)->where('ist_kinderhaus', false))
            ->with('teilnahme.person')->get()
            ->each(function (Abrechnung $a) use ($boerse, $bereits, &$mails) {
                $person = $a->teilnahme->person;
                if (! $person || $bereits->has($person->id)) {
                    return;
                }
                if (Postausgang::einplanen($person, 'ergebnis', $boerse, [
                    'verkaufte_artikel' => (string) $a->verkaufte_artikel,
                    'umsatz' => Geld::format($a->umsatz_cent),
                    'spende' => Geld::format($a->spende_cent),
                    'auszahlung' => Geld::format($a->auszahlung_cent),
                ])) {
                    $mails++;
                }
            });

        return back()->with('erfolg', "Ergebnis freigegeben. {$mails} Ergebnis-Mail(s) eingeplant.");
    }

    public function auszahlungsplan(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();

        return view('admin.abrechnung.auszahlungsplan', [
            'boerse' => $boerse,
            'plan' => new Auszahlungsplan($boerse),
            'vorherige' => Boerse::query()->where('verkaufstag', '<', $boerse->verkaufstag)->orderByDesc('verkaufstag')->first(),
        ]);
    }

    public function einnahmeSpeichern(Request $request, BoerseKontext $kontext): RedirectResponse
    {
        $daten = $request->validate([
            'art' => ['required', Rule::in(array_keys(SonstigeEinnahme::ARTEN))],
            'betrag' => ['required', 'string', 'max:20'],
            'notiz' => ['nullable', 'string', 'max:190'],
        ]);

        try {
            $cent = Geld::parse($daten['betrag']);
        } catch (\InvalidArgumentException) {
            return back()->withErrors(['betrag' => 'Bitte einen Betrag wie 123,50 eingeben.']);
        }

        $kontext->getOrFail()->sonstigeEinnahmen()->create([
            'art' => $daten['art'],
            'betrag_cent' => $cent,
            'notiz' => $daten['notiz'] ?? null,
            'erfasst_von' => $request->user()->id,
        ]);

        return back()->with('erfolg', 'Einnahme erfasst.');
    }

    public function einnahmeLoeschen(SonstigeEinnahme $einnahme): RedirectResponse
    {
        $einnahme->delete();

        return back()->with('erfolg', 'Einnahme gelöscht.');
    }

    /** @return array<string, int> */
    public static function summen(Boerse $boerse, $verkaeufer, ?Abrechnung $kinderhaus, int $sonstigeCent): array
    {
        $spende = (int) $verkaeufer->sum('spende_cent');
        $kinderhausErloes = (int) ($kinderhaus?->umsatz_cent ?? 0);

        return [
            'umsatz' => (int) $verkaeufer->sum('umsatz_cent') + $kinderhausErloes,
            'auszahlung' => (int) $verkaeufer->sum('auszahlung_cent'),
            'spende' => $spende,
            'kinderhaus' => $kinderhausErloes,
            'sonstige' => $sonstigeCent,
            'gesamtKinderhaus' => $spende + $kinderhausErloes + $sonstigeCent,
            'offen' => (int) $verkaeufer->whereNull('ausgezahlt_at')->sum('auszahlung_cent'),
        ];
    }
}
