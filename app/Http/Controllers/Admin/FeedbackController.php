<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BoerseKontext;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $antworten = $boerse->feedback()->whereNotNull('beantwortet_at')->latest('beantwortet_at')->get();

        return view('admin.feedback.index', [
            'boerse' => $boerse,
            'antworten' => $antworten,
            'verschickt' => $boerse->feedback()->count(),
            'verteilung' => collect(range(5, 1))->mapWithKeys(fn ($n) => [$n => $antworten->where('bewertung', $n)->count()]),
            'schnitt' => $antworten->whereNotNull('bewertung')->avg('bewertung'),
        ]);
    }
}
