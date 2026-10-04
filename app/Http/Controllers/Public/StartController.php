<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Seite;
use Database\Seeders\SeitenSeeder;
use Illuminate\View\View;

/** Die Startseite ist eine normale Baustein-Seite (slug "start") und im Backend bearbeitbar. */
class StartController extends Controller
{
    public function index(): View
    {
        $seite = Seite::query()->where('slug', 'start')->whereNotNull('bloecke')->first()
            ?? new Seite(['slug' => 'start', 'titel' => 'Startseite', 'bloecke' => SeitenSeeder::startBloecke()]);

        return SeiteController::bausteinAnsicht($seite, $seite->bloecke, $seite->titel);
    }
}
