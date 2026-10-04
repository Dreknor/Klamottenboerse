<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Statistik\BoersenStatistik;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Support\BoerseKontext;
use Illuminate\View\View;

class StatistikController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();

        $vergleich = Boerse::query()->orderBy('verkaufstag')->get()
            ->map(fn (Boerse $b) => ['boerse' => $b] + BoersenStatistik::fuer($b))
            ->filter(fn ($zeile) => $zeile['umsatz'] > 0 || $zeile['boerse']->is($boerse))
            ->values();

        return view('admin.statistik.index', [
            'boerse' => $boerse,
            'statistik' => BoersenStatistik::fuer($boerse),
            'vergleich' => $vergleich,
            'maxUmsatz' => max(1, $vergleich->max('umsatz')),
        ]);
    }
}
