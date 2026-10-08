<?php

namespace App\Models;

use App\Domain\Reputation\Reputation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Ein Vermerk zu einem Verkäufer (z. B. Termin verpasst) – zählt mit seinen Punkten zur Reputation. */
class Vermerk extends Model
{
    protected $table = 'vermerke';

    protected $guarded = ['id'];

    public const QUELLEN = [
        'backend' => 'Backend',
        'kasse' => 'Kasse',
        'annahme' => 'Annahme',
        'rueckpacken' => 'Rückpacken',
        'ausgabe' => 'Ausgabe',
    ];

    protected function casts(): array
    {
        return ['punkte' => 'integer'];
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function erfasser(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'erfasst_von');
    }

    /** Zählt noch: jünger als der eingestellte Zeitraum. */
    public function scopeWirksam(Builder $query): void
    {
        $query->where('created_at', '>=', now()->subMonths(Reputation::zeitraumMonate()));
    }

    public function istWirksam(): bool
    {
        return $this->created_at->gte(now()->subMonths(Reputation::zeitraumMonate()));
    }
}
