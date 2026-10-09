<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeedbackAntwort;
use App\Models\FeedbackFrage;
use App\Support\BoerseKontext;
use Illuminate\View\View;

/** Auswertung je Frage: Sterne als Verteilung, Auswahl als Häufigkeiten, Freitexte als Liste. */
class FeedbackController extends Controller
{
    public function index(BoerseKontext $kontext): View
    {
        $boerse = $kontext->getOrFail();
        $antworten = FeedbackAntwort::query()
            ->whereHas('feedback', fn ($q) => $q->where('boerse_id', $boerse->id))
            ->with('feedback:id,rolle,beantwortet_at')->get()->groupBy('feedback_frage_id');

        // aktive Fragen und alle, zu denen es bei dieser Börse Antworten gibt
        $fragen = FeedbackFrage::query()->sortiert()->get()
            ->filter(fn (FeedbackFrage $f) => $f->aktiv || $antworten->has($f->id))
            ->map(fn (FeedbackFrage $f) => [
                'frage' => $f,
                'antworten' => $liste = $antworten->get($f->id, collect())->sortByDesc('feedback.beantwortet_at')->values(),
                'schnitt' => $f->typ === 'sterne' ? $liste->avg('zahl') : null,
                'verteilung' => match ($f->typ) {
                    'sterne' => collect(range(5, 1))->mapWithKeys(fn ($n) => [str_repeat('★', $n) => $liste->where('zahl', $n)->count()]),
                    'auswahl' => collect($f->optionen ?? [])->merge($liste->pluck('text'))->unique()
                        ->mapWithKeys(fn ($o) => [$o => $liste->where('text', $o)->count()]),
                    default => null,
                },
            ]);

        return view('admin.feedback.index', [
            'boerse' => $boerse,
            'fragen' => $fragen,
            'beantwortet' => $boerse->feedback()->whereNotNull('beantwortet_at')->count(),
            'verschickt' => $boerse->feedback()->count(),
        ]);
    }
}
