<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Seite;
use Illuminate\View\View;

class SeiteController extends Controller
{
    public function impressum(): View
    {
        return $this->zeigen('impressum');
    }

    public function datenschutz(): View
    {
        return $this->zeigen('datenschutz');
    }

    private function zeigen(string $slug): View
    {
        return view('public.seite', ['seite' => Seite::query()->where('slug', $slug)->firstOrFail()]);
    }
}
