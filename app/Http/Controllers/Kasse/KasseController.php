<?php

namespace App\Http\Controllers\Kasse;

use App\Domain\Kasse\BonErfassen;
use App\Domain\Kasse\Warenkorb;
use App\Enums\BoerseStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Bon;
use App\Models\Kasse;
use App\Models\Kassenschicht;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Kasse: Der offene Einkauf liegt in der Datenbank (je Konto), damit z. B. am Handy gescannt
 * und am PC kassiert werden kann. Ohne Netz arbeitet die Kasse lokal weiter und gleicht später ab.
 */
class KasseController extends Controller
{
    public function index(): View
    {
        return view('kasse.index', ['boerse' => $this->boerse()]);
    }

    /** Daten für den Offline-Betrieb: gültige Nummern und im Portal erfasste Preise. */
    public function daten(): JsonResponse
    {
        $boerse = $this->boerse();

        $artikel = DB::table('artikel')
            ->join('teilnahmen', 'teilnahmen.id', '=', 'artikel.teilnahme_id')
            ->where('teilnahmen.boerse_id', $boerse->id)
            ->whereNull('artikel.deleted_at')
            ->get(['teilnahmen.nummer', 'artikel.laufnummer', 'artikel.preis_cent'])
            ->mapWithKeys(fn ($a) => ["{$a->nummer}-{$a->laufnummer}" => (int) $a->preis_cent]);

        return response()->json([
            'boerse_id' => $boerse->id,
            'nummern' => $boerse->teilnahmen()->mitNummer()->pluck('nummer')->map(fn ($n) => (int) $n)->values(),
            'artikel' => $artikel->isEmpty() ? new \stdClass : $artikel,
        ]);
    }

    /** Stand des gemeinsamen Warenkorbs – wird von allen Geräten des Kontos regelmäßig abgefragt. */
    public function warenkorb(Request $request): JsonResponse
    {
        return response()->json($this->stand($this->korb($request)));
    }

    public function hinzufuegen(Request $request): JsonResponse
    {
        $daten = $request->validate([
            'uuid' => ['required', 'uuid'],
            'nummer' => ['required', 'integer', 'min:1', 'max:999'],
            'artikel' => ['required', 'integer', 'min:0', 'max:999'],
            'preis_cent' => ['required', 'integer'],
        ]);
        if ($fehler = $this->gesperrt()) {
            return $fehler;
        }

        $korb = $this->korb($request);
        try {
            $ergebnis = $korb->hinzufuegen($daten['uuid'], $daten['nummer'], $daten['artikel'], $daten['preis_cent']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()] + $this->stand($korb), 422);
        }

        return response()->json(['warnungen' => $ergebnis['warnungen']] + $this->stand($korb), 201);
    }

    public function entfernen(Request $request, string $uuid): JsonResponse
    {
        $korb = $this->korb($request);
        $korb->entfernen($uuid);

        return response()->json($this->stand($korb));
    }

    public function leeren(Request $request): JsonResponse
    {
        $korb = $this->korb($request);
        $korb->leeren();

        return response()->json($this->stand($korb));
    }

    public function abschliessen(Request $request): JsonResponse
    {
        $daten = $request->validate(['bon_uuid' => ['required', 'uuid'], 'kasse' => ['nullable', 'string', 'max:40']]);
        if ($fehler = $this->gesperrt()) {
            return $fehler;
        }

        $korb = $this->korb($request);
        try {
            $bon = $korb->abschliessen($this->schicht($request, $this->boerse(), $daten['kasse'] ?? null), $daten['bon_uuid']);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()] + $this->stand($korb), 422);
        }

        return response()->json(['bon' => ['uuid' => $bon->uuid, 'summe_cent' => $bon->summe_cent]] + $this->stand($korb), 201);
    }

    /** Offline abgeschlossene Bons nachträglich speichern (idempotent über die UUID). */
    public function sync(Request $request, BonErfassen $erfassen): JsonResponse
    {
        $daten = $request->validate([
            'uuid' => ['required', 'uuid'],
            'erstellt_am' => ['required', 'date'],
            'kasse' => ['nullable', 'string', 'max:40'],
            'positionen' => ['required', 'array', 'min:1', 'max:200'],
            'positionen.*.uuid' => ['nullable', 'uuid'],
            'positionen.*.nummer' => ['required', 'integer'],
            'positionen.*.artikel' => ['required', 'integer', 'min:0'],
            'positionen.*.preis_cent' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);
        if ($fehler = $this->gesperrt()) {
            return $fehler;
        }

        $boerse = $this->boerse();
        try {
            $bon = $erfassen($boerse, $this->schicht($request, $boerse, $daten['kasse'] ?? null), $daten);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['uuid' => $bon->uuid, 'summe_cent' => $bon->summe_cent], 201);
    }

    /** Letzten Einkauf korrigieren: Bon stornieren, Artikel zurück in den Warenkorb. */
    public function zurueckholen(Request $request, Bon $bon): JsonResponse
    {
        $korb = $this->korb($request);
        try {
            $korb->bonZurueckholen($bon);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()] + $this->stand($korb), 422);
        }

        return response()->json($this->stand($korb));
    }

    private function korb(Request $request): Warenkorb
    {
        return new Warenkorb($request->user(), $this->boerse());
    }

    /** @return array{positionen: list<array>, summe_cent:int, letzter_bon: ?array} */
    private function stand(Warenkorb $korb): array
    {
        $positionen = $korb->positionen();
        $bon = $korb->letzterBon();

        return [
            'positionen' => $positionen->map->alsArray()->values()->all(),
            'summe_cent' => (int) $positionen->sum('preis_cent'),
            'letzter_bon' => $bon ? ['uuid' => $bon->uuid, 'summe_cent' => $bon->summe_cent, 'um' => $bon->created_at->format('H:i'), 'storniert' => $bon->storniert_at !== null] : null,
        ];
    }

    private function gesperrt(): ?JsonResponse
    {
        return $this->boerse()->status === BoerseStatus::Abgeschlossen
            ? response()->json(['message' => 'Diese Börse ist abgeschlossen.'], 422)
            : null;
    }

    private function boerse(): Boerse
    {
        return Boerse::aktuelle() ?? abort(404, 'Es gibt keine aktuelle Börse.');
    }

    private function schicht(Request $request, Boerse $boerse, ?string $kassenname): Kassenschicht
    {
        $kasse = Kasse::query()->firstOrCreate(
            ['name' => $kassenname ?: 'Kasse '.$request->user()->vorname],
            ['geraete_token' => Str::random(40)],
        );
        $kasse->update(['zuletzt_gesehen_at' => now()]);

        return Kassenschicht::query()->firstOrCreate(
            ['kasse_id' => $kasse->id, 'boerse_id' => $boerse->id, 'person_id' => $request->user()->id, 'ende' => null],
            ['beginn' => now()],
        );
    }
}
