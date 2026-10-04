<?php

namespace App\Models;

use App\Enums\EinteilungStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schicht extends Model
{
    public const BEREICHE = ['Aufbau', 'Annahme', 'Sortieren', 'Kasse', 'Café', 'Rückpacken', 'Ausgabe', 'Abbau'];

    protected $table = 'schichten';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['beginn' => 'datetime', 'ende' => 'datetime'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function einteilungen(): HasMany
    {
        return $this->hasMany(Einteilung::class);
    }

    public function zusagen(): HasMany
    {
        return $this->einteilungen()->where('status', EinteilungStatus::Zugesagt);
    }

    public function freiePlaetze(): int
    {
        return max(0, $this->soll - $this->zusagen()->count());
    }
}
