<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Art eines Reputations-Vermerks (z. B. "Kiste nicht gebracht") mit
 * Standardpunkten. Vom Orga-Team pflegbar; deaktivierte Arten können nicht
 * mehr neu erfasst werden, bestehende Vermerke behalten aber ihr Label.
 */
class VermerkTyp extends Model
{
    public $table = 'vermerk_typen';

    protected $fillable = ['schluessel', 'label', 'punkte', 'sortierung', 'aktiv'];

    protected $casts = [
        'punkte' => 'integer',
        'sortierung' => 'integer',
        'aktiv' => 'boolean',
    ];

    private const CACHE_KEY = 'vermerk_typen.alle';

    protected static function booted()
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * Alle Arten (inkl. inaktiver), sortiert, als Collection keyed by schluessel.
     */
    public static function alle()
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()
            ->orderBy('sortierung')
            ->orderBy('label')
            ->get()
        )->keyBy('schluessel');
    }

    public static function aktive()
    {
        return static::alle()->where('aktiv', true);
    }
}
