<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Teilnahme\Actions\Absagen;
use App\Enums\EinteilungStatus;
use App\Http\Controllers\Admin\AblageController;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Kategorie;
use App\Models\Ordner;
use App\Models\Teilnahme;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Persönliche Übersicht für Verkäufer und Helfer. */
class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $person = $request->user();
        $teilnahme = self::aktuelleTeilnahme($request);
        $boerse = $teilnahme?->boerse ?? Boerse::query()->offen()->where('verkaufstag', '>=', today())->orderBy('verkaufstag')->first();

        $verkauft = collect();
        if ($teilnahme && ($boerse->live_erloes_freigegeben || $boerse->ergebnis_freigegeben)) {
            $verkauft = $teilnahme->gueltigePositionen()->orderBy('artikelnummer')->get();
        }

        return view('portal.index', [
            'person' => $person,
            'boerse' => $boerse,
            'teilnahme' => $teilnahme?->load(['artikel.kategorie', 'abrechnung', 'kisten']),
            'verkauft' => $verkauft,
            'kategorien' => Kategorie::query()->aktiv()->sortiert()->pluck('name', 'id'),
            'schichten' => $person->einteilungen()->where('status', EinteilungStatus::Zugesagt)
                ->whereHas('schicht', fn ($q) => $q->where('beginn', '>=', today()))
                ->with('schicht')->get(),
            'unterlagen' => self::darfUnterlagenSehen($request)
                ? Ordner::query()->where('fuer_helfer', true)->with('media')->orderBy('name')->get()
                : collect(),
            'fruehere' => $person->teilnahmen()->with(['boerse', 'abrechnung'])
                ->when($teilnahme, fn ($q) => $q->whereKeyNot($teilnahme->id))
                ->whereHas('abrechnung')->get()->sortByDesc(fn ($t) => $t->boerse->verkaufstag),
        ]);
    }

    /** Datei aus einem für Helfer freigegebenen Ordner – nur für Personen mit Schicht oder Teilnahme. */
    public function unterlage(Request $request, Media $media): BinaryFileResponse
    {
        $ordner = $media->model;
        abort_unless($ordner instanceof Ordner && $ordner->sichtbarFuerHelfer() && self::darfUnterlagenSehen($request), 404);

        return AblageController::ausliefern($media, $request->boolean('vorschau'));
    }

    public static function darfUnterlagenSehen(Request $request): bool
    {
        $person = $request->user();

        return $person->roles()->exists()
            || $person->einteilungen()->where('status', EinteilungStatus::Zugesagt)->whereHas('schicht', fn ($q) => $q->where('beginn', '>=', today()->subDays(7)))->exists();
    }

    public function absagen(Request $request, Absagen $absagen): RedirectResponse
    {
        $teilnahme = self::aktuelleTeilnahme($request) ?? abort(404);

        try {
            $absagen($teilnahme, 'verkaeufer');
        } catch (DomainException $e) {
            return back()->with('fehler', $e->getMessage());
        }

        return back()->with('erfolg', 'Du hast abgesagt. Danke für die Rückmeldung!');
    }

    /** Teilnahme der angemeldeten Person an der nächsten (oder gerade laufenden) Börse. */
    public static function aktuelleTeilnahme(Request $request): ?Teilnahme
    {
        return Teilnahme::query()
            ->where('person_id', $request->user()->id)
            ->whereHas('boerse', fn ($q) => $q->where('verkaufstag', '>=', today()->subDays(14))->offen())
            ->with('boerse.ort')
            ->get()
            ->sortBy(fn ($t) => $t->boerse->verkaufstag)
            ->first();
    }
}
