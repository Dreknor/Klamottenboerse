<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Kategorie, die Verkäufer bei Registrierung / im Portal als "bringe ich
 * überwiegend mit" angeben können. Vom Orga-Team pflegbar. Mit Größenbereich
 * (groesse_von/-bis) werden im Portal erfasste Artikel automatisch zugeordnet.
 */
class Angebotskategorie extends Model
{
    public $table = 'angebotskategorien';

    protected $fillable = ['schluessel', 'label', 'gruppe', 'groesse_von', 'groesse_bis', 'sortierung', 'aktiv'];

    protected $casts = [
        'groesse_von' => 'integer',
        'groesse_bis' => 'integer',
        'sortierung' => 'integer',
        'aktiv' => 'boolean',
    ];

    private const CACHE_KEY = 'angebotskategorien.alle';

    protected static function booted()
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    public function hatGroessenbereich(): bool
    {
        return $this->groesse_von !== null && $this->groesse_bis !== null;
    }

    /**
     * Alle Kategorien (inkl. inaktiver), sortiert, keyed by schluessel.
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
