<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Art eines Vermerks mit Punkten, z. B. „Kiste(n) nicht gebracht“ = 3 Punkte. Vom Orga-Team pflegbar. */
class VermerkArt extends Model
{
    protected $table = 'vermerk_arten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['punkte' => 'integer', 'sortierung' => 'integer', 'aktiv' => 'boolean'];
    }

    public function scopeAktiv(Builder $query): void
    {
        $query->where('aktiv', true)->orderBy('sortierung')->orderBy('name');
    }
}
