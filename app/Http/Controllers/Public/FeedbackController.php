<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\FeedbackFrage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Kurze Umfrage nach der Börse – Link mit persönlichem Token aus der Mail. Die Fragen pflegt das Orga-Team. */
class FeedbackController extends Controller
{
    public function show(string $token): View
    {
        $feedback = Feedback::query()->where('token', $token)->with('boerse', 'antworten')->firstOrFail();

        return view('public.feedback', [
            'feedback' => $feedback,
            'fragen' => $this->fragen($feedback),
            'antworten' => $feedback->antworten->keyBy('feedback_frage_id'),
        ]);
    }

    public function store(Request $request, string $token): View
    {
        $feedback = Feedback::query()->where('token', $token)->firstOrFail();
        $fragen = $this->fragen($feedback);

        $regeln = $fragen->mapWithKeys(fn (FeedbackFrage $f) => ["antworten.{$f->id}" => [
            $f->pflicht ? 'required' : 'nullable',
            ...match ($f->typ) {
                'sterne' => ['integer', 'between:1,5'],
                'auswahl' => [Rule::in($f->optionen ?? [])],
                default => ['string', 'max:2000'],
            },
        ]])->all();
        $namen = $fragen->mapWithKeys(fn (FeedbackFrage $f) => ["antworten.{$f->id}" => "„{$f->text}“"])->all();
        $werte = $request->validate($regeln, [], $namen)['antworten'] ?? [];

        DB::transaction(function () use ($feedback, $fragen, $werte) {
            foreach ($fragen as $frage) {
                $wert = $werte[$frage->id] ?? null;
                if ($wert === null || $wert === '') {
                    $feedback->antworten()->where('feedback_frage_id', $frage->id)->delete();

                    continue;
                }
                $feedback->antworten()->updateOrCreate(['feedback_frage_id' => $frage->id], $frage->typ === 'sterne'
                    ? ['zahl' => (int) $wert, 'text' => null]
                    : ['zahl' => null, 'text' => $wert]);
            }
            $feedback->update(['beantwortet_at' => now()]);
        });

        return view('public.hinweis', ['titel' => 'Danke!', 'text' => 'Dein Feedback hilft uns, die nächste Börse noch besser zu machen.']);
    }

    /** @return Collection<int, FeedbackFrage> */
    private function fragen(Feedback $feedback): Collection
    {
        return FeedbackFrage::query()->fuer($feedback->rolle)->sortiert()->get();
    }
}
