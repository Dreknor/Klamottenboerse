<?php

namespace App\Http\Controllers\Public;

use App\Domain\Kommunikation\Platzhalter;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use Illuminate\View\View;

class StartController extends Controller
{
    public function index(): View
    {
        $boerse = Boerse::query()->offen()->where('verkaufstag', '>=', today())->orderBy('verkaufstag')->with('ort')->first();

        return view('public.start', [
            'boerse' => $boerse,
            'infos' => $boerse ? Platzhalter::fuer(null, $boerse) : [],
            'freieSchichten' => $boerse ? $boerse->schichten()->withCount('zusagen')->get()->sum(fn ($s) => max(0, $s->soll - $s->zusagen_count)) : 0,
        ]);
    }
}
