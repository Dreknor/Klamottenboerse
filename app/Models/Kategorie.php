<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategorie des Angebots, z. B. „Kleidung Gr. 74–92“ oder „Spielzeug & Spiele“.
 * Verkäufer geben bei der Anmeldung an, was sie überwiegend mitbringen; Artikel werden einer Kategorie
 * zugeordnet (gewählt oder – bei Kleidung – automatisch über den Größenbereich).
 */
class Kategorie extends Model
{
    protected $table = 'kategorien';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'groesse_von' => 'integer',
            'groesse_bis' => 'integer',
            'sortierung' => 'integer',
            'aktiv' => 'boolean',
        ];
    }

    public function personen(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'kategorie_person');
    }

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class);
    }

    public function scopeAktiv(Builder $query): void
    {
        $query->where('aktiv', true);
    }

    public function scopeSortiert(Builder $query): void
    {
        $query->orderBy('sortierung')->orderBy('name');
    }

    /** @return Collection<string, Collection<int, Kategorie>> aktive Kategorien, nach Gruppe */
    public static function auswahl(): Collection
    {
        return static::query()->aktiv()->sortiert()->get()->groupBy('gruppe');
    }

    public function hatGroessenbereich(): bool
    {
        return $this->groesse_von !== null && $this->groesse_bis !== null;
    }

    /** Passende Kategorie zu einer Größenangabe wie „86/92“ (erste Zahl zählt) – wie in V1. */
    public static function fuerGroesse(?string $groesse): ?self
    {
        if (! $groesse || ! preg_match('/\d+/', $groesse, $treffer)) {
            return null;
        }
        $zahl = (int) $treffer[0];

        return static::query()->aktiv()->sortiert()
            ->whereNotNull('groesse_von')->whereNotNull('groesse_bis')
            ->where('groesse_von', '<=', $zahl)->where('groesse_bis', '>=', $zahl)
            ->first();
    }
}
