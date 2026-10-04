<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Drei kurze Fragen nach der Börse – Link mit persönlichem Token aus der Mail. */
class FeedbackController extends Controller
{
    public function show(string $token): View
    {
        return view('public.feedback', ['feedback' => Feedback::query()->where('token', $token)->with('boerse')->firstOrFail()]);
    }

    public function store(Request $request, string $token): View
    {
        $feedback = Feedback::query()->where('token', $token)->firstOrFail();

        $feedback->update($request->validate([
            'bewertung' => ['nullable', 'integer', 'between:1,5'],
            'gut' => ['nullable', 'string', 'max:2000'],
            'besser' => ['nullable', 'string', 'max:2000'],
        ]) + ['beantwortet_at' => now()]);

        return view('public.hinweis', ['titel' => 'Danke!', 'text' => 'Dein Feedback hilft uns, die nächste Börse noch besser zu machen.']);
    }
}
