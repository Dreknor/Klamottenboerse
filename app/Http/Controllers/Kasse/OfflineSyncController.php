<?php

namespace App\Http\Controllers\Kasse;

use App\Http\Controllers\Controller;
use App\Models\OfflineSaleQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfflineSyncController extends Controller
{
    public function sync(Request $request)
    {
        $payload = $request->input('sales', []);

        if (! is_array($payload)) {
            return response()->json([
                'ok' => false,
                'message' => 'Invalid payload.',
            ], 422);
        }

        $created = 0;

        foreach ($payload as $sale) {
            if (! is_array($sale) || empty($sale['vknummer']) || empty($sale['artikelnummer'])) {
                continue;
            }

            OfflineSaleQueue::create([
                'user_id' => Auth::id() ?? $sale['user_id'] ?? 0,
                'device_id' => $request->header('X-Device-Id', 'unknown-device'),
                'payload' => $sale,
                'status' => 'pending',
            ]);

            $created++;
        }

        return response()->json([
            'ok' => true,
            'count' => $created,
        ]);
    }
}
