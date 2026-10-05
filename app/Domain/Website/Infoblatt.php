<?php

namespace App\Domain\Website;

use App\Models\Boerse;
use App\Models\Seite;
use App\Support\Einstellungen;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\SeitenSeeder;
use Illuminate\Http\Response;

/**
 * „Wichtige Infos für Verkäufer“ als PDF – gebaut aus der Website-Seite „Verkäufer-Info“.
 * Was auf der Website geändert wird, steht damit automatisch auch im Infoblatt.
 */
class Infoblatt
{
    public const SEITE = 'verkaeufer-info';

    public static function pdf(?Boerse $boerse = null): Response
    {
        $seite = Seite::query()->where('slug', self::SEITE)->first();
        $kontext = new SeitenKontext($seite, $boerse);

        return Pdf::loadView('pdf.infoblatt', [
            'k' => $kontext,
            'bloecke' => Bausteine::bereinigen($seite?->bloecke ?? SeitenSeeder::VERKAEUFER_INFO),
            'kontakt' => ['email' => Einstellungen::get('kontakt_email'), 'telefon' => Einstellungen::get('kontakt_telefon')],
        ])->setPaper('a4')->stream('Infoblatt-Verkaeufer'.($kontext->boerse ? '-'.$kontext->boerse->verkaufstag->format('Y-m-d') : '').'.pdf');
    }
}
