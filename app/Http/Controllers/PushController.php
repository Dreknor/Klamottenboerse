<?php

namespace App\Http\Controllers;

use App\Domain\Push\Push;
use App\Models\PushAbo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Push-Nachrichten auf diesem Gerät ein- und ausschalten (Portal und Backend). */
class PushController extends Controller
{
    public function speichern(Request $request): JsonResponse
    {
        $daten = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        PushAbo::query()->updateOrCreate(
            ['endpoint_hash' => hash('sha256', $daten['endpoint'])],
            [
                'person_id' => $request->user()->id,
                'endpoint' => $daten['endpoint'],
                'p256dh' => $daten['keys']['p256dh'],
                'auth' => $daten['keys']['auth'],
                'geraet' => Str::limit($this->geraet((string) $request->userAgent()), 190),
            ],
        );

        return response()->json(['ok' => true]);
    }

    public function loeschen(Request $request): JsonResponse
    {
        $endpoint = (string) $request->validate(['endpoint' => ['required', 'string']])['endpoint'];
        PushAbo::query()->where('endpoint_hash', hash('sha256', $endpoint))->where('person_id', $request->user()->id)->delete();

        return response()->json(['ok' => true]);
    }

    /** Testnachricht an alle Geräte der Person – sofort, ohne auf den Zeitplan zu warten. */
    public function test(Request $request): JsonResponse
    {
        Push::einplanen($request->user(), 'Klamottenbörse', 'Push-Nachrichten funktionieren auf diesem Gerät. 👍', url('/'));
        $anzahl = Push::versenden();

        return response()->json(['ok' => $anzahl > 0]);
    }

    private function geraet(string $agent): string
    {
        $system = match (true) {
            str_contains($agent, 'iPhone') || str_contains($agent, 'iPad') => 'iPhone/iPad',
            str_contains($agent, 'Android') => 'Android',
            str_contains($agent, 'Windows') => 'Windows',
            str_contains($agent, 'Mac OS') => 'Mac',
            default => 'Gerät',
        };
        $browser = match (true) {
            str_contains($agent, 'Edg/') => 'Edge',
            str_contains($agent, 'Firefox') => 'Firefox',
            str_contains($agent, 'Chrome') => 'Chrome',
            str_contains($agent, 'Safari') => 'Safari',
            default => 'Browser',
        };

        return "{$browser} auf {$system}";
    }
}
