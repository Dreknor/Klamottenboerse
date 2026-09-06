<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vom Verkäufer selbst vor dem Event erfasste Verkaufsartikel
 * (Verkäufer-Self-Service-Portal). Dient als Grundlage für den
 * Etiketten-Druck; die eigentliche Preis-Erfassung an der Kasse bleibt
 * davon unberührt.
 */
class Verkaufsartikel extends Model
{
    use SoftDeletes;

    public $table = 'verkaufsartikel';

    protected $fillable = ['vknummer_id', 'artikelnummer', 'beschreibung', 'kategorie', 'groesse', 'preis'];

    protected $casts = [
        'preis' => 'float',
    ];

    public function vknummer()
    {
        return $this->belongsTo(VKnummer::class, 'vknummer_id');
    }

    /**
     * Nächste freie Artikelnummer für die angegebene VK-Nummer ermitteln
     * (fortlaufend je Verkäufer, beginnend bei 1).
     */
    public static function naechsteArtikelnummer(int $vknummerId): int
    {
        $letzte = static::withTrashed()
            ->where('vknummer_id', $vknummerId)
            ->max('artikelnummer');

        return ((int) $letzte) + 1;
    }
}
