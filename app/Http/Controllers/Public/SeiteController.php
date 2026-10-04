<?php

namespace App\Http\Controllers\Public;

use App\Domain\Website\SeitenKontext;
use App\Http\Controllers\Controller;
use App\Models\Seite;
use Illuminate\View\View;

class SeiteController extends Controller
{
    public function impressum(): View
    {
        return $this->anzeigen(Seite::query()->where('slug', 'impressum')->firstOrFail());
    }

    public function datenschutz(): View
    {
        return $this->anzeigen(Seite::query()->where('slug', 'datenschutz')->firstOrFail());
    }

    /** Weitere Seiten, z. B. /verkaeufer-info oder /faq */
    public function zeigen(Seite $seite): View
    {
        abort_if($seite->veroeffentlicht_at === null || $seite->slug === 'start', 404);

        return $this->anzeigen($seite);
    }

    public static function bausteinAnsicht(Seite $seite, array $bloecke, string $titel, bool $vorschau = false): View
    {
        return view('public.baustein-seite', [
            'seite' => $seite,
            'titel' => $titel,
            'bloecke' => $bloecke,
            'k' => new SeitenKontext($seite),
            'vorschau' => $vorschau,
        ]);
    }

    private function anzeigen(Seite $seite): View
    {
        return $seite->bloecke !== null
            ? self::bausteinAnsicht($seite, $seite->bloecke, $seite->titel)
            : view('public.seite', ['seite' => $seite]);
    }
}
