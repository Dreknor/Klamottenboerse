<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Einfache, vom Orga-Team pflegbare Schlüssel/Wert-Einstellungen
 * (z. B. Schwellen der Verkäufer-Reputation).
 */
class Einstellung extends Model
{
    public $table = 'einstellungen';

    protected $fillable = ['schluessel', 'wert'];

    private const CACHE_KEY = 'einstellungen.alle';

    /**
     * Startwerte, falls ein Schlüssel (noch) nicht in der Datenbank steht.
     */
    public const STANDARD = [
        'reputation_sperre_ab' => 5,
        'reputation_warnung_ab' => 2,
        'reputation_zeitraum_monate' => 24,
    ];

    protected static function booted()
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public static function wert(string $schluessel)
    {
        $alle = Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->pluck('wert', 'schluessel')->all());

        return $alle[$schluessel] ?? (self::STANDARD[$schluessel] ?? null);
    }

    public static function zahl(string $schluessel): int
    {
        return (int) static::wert($schluessel);
    }

    public static function setze(string $schluessel, $wert): void
    {
        static::query()->updateOrCreate(['schluessel' => $schluessel], ['wert' => $wert]);
    }
}
