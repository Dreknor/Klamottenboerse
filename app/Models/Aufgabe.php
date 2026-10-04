<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aufgabe extends Model
{
    protected $table = 'aufgaben';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['faellig_am' => 'date', 'erledigt_at' => 'datetime', 'erinnert_at' => 'datetime'];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function zustaendig(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'zustaendig_id');
    }

    public function erledigtVon(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'erledigt_von');
    }

    public function scopeOffen(Builder $query): void
    {
        $query->whereNull('erledigt_at');
    }

    public function istUeberfaellig(): bool
    {
        return $this->erledigt_at === null && $this->faellig_am !== null && $this->faellig_am->isBefore(today());
    }
}
