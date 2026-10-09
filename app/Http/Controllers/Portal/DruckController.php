<?php

namespace App\Http\Controllers\Portal;

use App\Enums\EtikettVorlage;
use App\Http\Controllers\Controller;
use App\Support\Geld;
use App\Support\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

/** PDFs zum Ausdrucken zu Hause – vor Ort kann nichts gedruckt werden. */
class DruckController extends Controller
{
    /**
     * Etiketten auf dem gewählten Bogen. Mit `artikel[]` nur einzelne Etiketten (Nachdruck), mit `start`
     * beginnt der Druck auf einem angebrochenen Bogen beim ersten freien Feld.
     */
    public function etiketten(Request $request): Response
    {
        $teilnahme = PortalController::aktuelleTeilnahme($request);
        abort_unless($teilnahme && $teilnahme->hatNummer(), 404);

        $daten = $request->validate([
            'vorlage' => ['nullable', Rule::enum(EtikettVorlage::class)],
            'start' => ['nullable', 'integer', 'min:1'],
            'artikel' => ['nullable', 'array'],
            'artikel.*' => ['integer'],
        ]);

        $person = $request->user();
        $vorlage = isset($daten['vorlage']) ? EtikettVorlage::from($daten['vorlage']) : ($person->etikett_vorlage ?? EtikettVorlage::STANDARD);
        if ($person->etikett_vorlage !== $vorlage) {
            $person->forceFill(['etikett_vorlage' => $vorlage])->save();
        }
        $start = min((int) ($daten['start'] ?? 1), $vorlage->proBogen());

        $artikel = $teilnahme->artikel()
            ->when($daten['artikel'] ?? null, fn ($q, $ids) => $q->whereKey($ids))
            ->orderBy('laufnummer')->get();
        abort_if($artikel->isEmpty(), 404);

        $etiketten = $artikel->each->setRelation('teilnahme', $teilnahme)->map(fn ($artikel) => [
            'nummer' => $teilnahme->nummer,
            'laufnummer' => $artikel->laufnummer,
            'beschreibung' => $artikel->beschreibung,
            'groesse' => $artikel->groesse,
            'preis' => Geld::format($artikel->preis_cent),
            'qr' => QrCode::png($artikel->etikettCode(), 4),
        ]);

        // Bereits benutzte Felder auf dem ersten Bogen frei lassen
        $felder = collect(array_fill(0, $start - 1, null))->concat($etiketten);

        return Pdf::loadView('pdf.etiketten', ['seiten' => $felder->chunk($vorlage->proBogen()), 'vorlage' => $vorlage])
            ->setPaper('a4')
            ->stream("etiketten-{$teilnahme->nummer}.pdf");
    }

    public function kistenzettel(Request $request): Response
    {
        $teilnahme = PortalController::aktuelleTeilnahme($request);
        abort_unless($teilnahme && $teilnahme->hatNummer(), 404);

        return Pdf::loadView('pdf.kistenzettel', [
            'teilnahme' => $teilnahme->load('person', 'boerse'),
            'qr' => QrCode::png((string) $teilnahme->nummer, 8),
            'anzahl' => max(1, (int) ($teilnahme->boerse->max_kisten ?? 2)),
        ])->setPaper('a4', 'landscape')->stream("kistenzettel-{$teilnahme->nummer}.pdf");
    }
}
