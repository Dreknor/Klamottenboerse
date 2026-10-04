<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\Geld;
use App\Support\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** PDFs zum Ausdrucken zu Hause – vor Ort kann nichts gedruckt werden. */
class DruckController extends Controller
{
    public function etiketten(Request $request): Response
    {
        $teilnahme = PortalController::aktuelleTeilnahme($request);
        abort_unless($teilnahme && $teilnahme->hatNummer(), 404);

        $etiketten = $teilnahme->artikel()->get()->each->setRelation('teilnahme', $teilnahme)->map(fn ($artikel) => [
            'nummer' => $teilnahme->nummer,
            'laufnummer' => $artikel->laufnummer,
            'beschreibung' => $artikel->beschreibung,
            'groesse' => $artikel->groesse,
            'preis' => Geld::format($artikel->preis_cent),
            'qr' => QrCode::png($artikel->etikettCode(), 4),
        ]);

        return Pdf::loadView('pdf.etiketten', ['etiketten' => $etiketten, 'teilnahme' => $teilnahme])
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
