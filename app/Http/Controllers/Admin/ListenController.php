<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Kommunikation\Platzhalter;
use App\Domain\Website\Infoblatt;
use App\Enums\EinteilungStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Teilnahme;
use App\Support\Belehrung;
use App\Support\BoerseKontext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Druckbare Listen wie in V1: Verkäuferliste, Belehrungen zum Unterschreiben, Abstreichliste, Helferliste. */
class ListenController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();

        return view('admin.listen.index', [
            'boerse' => $boerse,
            'anzahl' => $this->verkaeufer($boerse)->count(),
            'helfer' => $boerse->schichten()->withCount(['einteilungen' => fn ($q) => $q->where('status', EinteilungStatus::Zugesagt)])->get()->sum('einteilungen_count'),
        ]);
    }

    /** Verkäufer je 100er-Block mit Telefon und Platz für Bemerkungen (eine Seite je Block). */
    public function verkaeuferliste(BoerseKontext $kontext): Response
    {
        $boerse = $kontext->getOrFail();

        return $this->pdf('pdf.verkaeuferliste', $boerse, 'Verkaeuferliste', [
            'bloecke' => $this->verkaeufer($boerse, mitKinderhaus: true)->groupBy(fn (Teilnahme $t) => intdiv($t->nummer, 100) * 100),
        ]);
    }

    /** Belehrungen zum Unterschreiben bei der Kistenabgabe – zwei je A4-Seite, alle oder eine Nummer. */
    public function belehrungen(Request $request, BoerseKontext $kontext): Response
    {
        $boerse = $kontext->getOrFail();
        $teilnahmen = $this->verkaeufer($boerse)
            ->when($request->filled('nummer'), fn ($t) => $t->where('nummer', (int) $request->query('nummer')))
            ->values();
        abort_if($teilnahmen->isEmpty(), 404, 'Keine Verkäufer mit dieser Nummer.');

        $vorlage = $boerse->belehrung ?: Belehrung::STANDARD;
        $texte = $teilnahmen->mapWithKeys(fn (Teilnahme $t) => [
            $t->id => Str::markdown(Platzhalter::ersetzen($vorlage, Platzhalter::fuer($t->person, $boerse)), ['html_input' => 'escape']),
        ]);

        return $this->pdf('pdf.belehrungen', $boerse, 'Belehrungen'.($request->filled('nummer') ? '-'.$request->query('nummer') : ''), [
            'teilnahmen' => $teilnahmen,
            'texte' => $texte,
        ]);
    }

    /** Nur die Nummern in Spalten je Block – zum Abhaken bei Annahme und Ausgabe. */
    public function abstreichliste(BoerseKontext $kontext): Response
    {
        $boerse = $kontext->getOrFail();

        return $this->pdf('pdf.abstreichliste', $boerse, 'Abstreichliste', [
            'bloecke' => $this->verkaeufer($boerse, mitKinderhaus: true)->groupBy(fn (Teilnahme $t) => intdiv($t->nummer, 100) * 100),
        ]);
    }

    /** Helfer je Schicht mit Telefon. */
    public function helferliste(BoerseKontext $kontext): Response
    {
        $boerse = $kontext->getOrFail();
        $schichten = $boerse->schichten()->orderBy('beginn')->orderBy('bereich')
            ->with(['einteilungen' => fn ($q) => $q->where('status', EinteilungStatus::Zugesagt)->with('person')])->get();

        return $this->pdf('pdf.helferliste', $boerse, 'Helferliste', ['schichten' => $schichten]);
    }

    /** Infoblatt „Wichtige Infos für Verkäufer“ – Inhalt aus der Website-Seite, Daten der gewählten Börse. */
    public function infoblatt(BoerseKontext $kontext): Response
    {
        return Infoblatt::pdf($kontext->getOrFail());
    }

    /** @return Collection<int, Teilnahme> */
    private function verkaeufer(Boerse $boerse, bool $mitKinderhaus = false): Collection
    {
        return $boerse->teilnahmen()->mitNummer()
            ->when(! $mitKinderhaus, fn ($q) => $q->where('ist_kinderhaus', false))
            ->with('person')->orderBy('nummer')->get();
    }

    /** @param array<string, mixed> $daten */
    private function pdf(string $view, Boerse $boerse, string $name, array $daten): Response
    {
        return Pdf::loadView($view, ['boerse' => $boerse] + $daten)
            ->setPaper('a4')
            ->stream($name.'-'.$boerse->verkaufstag->format('Y-m-d').'.pdf');
    }
}
