<?php

namespace App\Http\Controllers\Kasse;

use App\Domain\Kasse\BonErfassen;
use App\Domain\Kasse\Stornieren;
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

    public function sync(Request $request, BonErfassen $erfassen): JsonResponse
    {
        $daten = $request->validate([
            'uuid' => ['required', 'uuid'],
            'erstellt_am' => ['required', 'date'],
            'kasse' => ['nullable', 'string', 'max:40'],
            'positionen' => ['required', 'array', 'min:1', 'max:200'],
            'positionen.*.nummer' => ['required', 'integer'],
            'positionen.*.artikel' => ['required', 'integer', 'min:0'],
            'positionen.*.preis_cent' => ['required', 'integer', 'min:1', 'max:99999'],
        ]);

        $boerse = $this->boerse();
        if ($boerse->status === BoerseStatus::Abgeschlossen) {
            return response()->json(['message' => 'Diese Börse ist abgeschlossen'], 422);
        }

        try {
            $bon = $erfassen($boerse, $this->schicht($request, $boerse, $daten['kasse'] ?? null), $daten);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['uuid' => $bon->uuid, 'summe_cent' => $bon->summe_cent], 201);
    }

    public function storno(Request $request, Bon $bon, Stornieren $stornieren): JsonResponse
    {
        $grund = $request->validate(['grund' => ['required', 'string', 'max:190']])['grund'];

        try {
            $stornieren->bon($bon, $grund);
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['ok' => true]);
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
