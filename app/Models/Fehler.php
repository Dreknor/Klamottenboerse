<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ein Eintrag im Fehlerprotokoll. Gleiche Fehler werden zusammengefasst (anzahl). */
class Fehler extends Model
{
    use MassPrunable;

    protected $table = 'fehlerprotokoll';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'kontext' => 'array',
            'zuletzt_at' => 'datetime',
            'erledigt_at' => 'datetime',
        ];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /** Erledigtes nach 30 Tagen, alles andere nach 90 Tagen ohne Wiederholung löschen. */
    public function prunable(): Builder
    {
        return static::query()->where(fn ($q) => $q
            ->where('erledigt_at', '<', now()->subDays(30))
            ->orWhere('zuletzt_at', '<', now()->subDays(90)));
    }

    public function farbe(): string
    {
        return match ($this->stufe) {
            'emergency', 'alert', 'critical', 'error' => 'red',
            'warning' => 'amber',
            default => 'stone',
        };
    }
}
