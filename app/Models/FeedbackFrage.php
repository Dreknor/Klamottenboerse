<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Frage im Feedback nach der Börse – vom Orga-Team gepflegt. Typen: Sterne (1–5), Freitext, Auswahl.
 * Fragen mit Antworten werden nicht gelöscht, sondern ausgeblendet, damit frühere Auswertungen erhalten bleiben.
 */
class FeedbackFrage extends Model
{
    public const TYPEN = ['sterne' => 'Sterne (1–5)', 'text' => 'Freitext', 'auswahl' => 'Auswahl'];

    public const ROLLEN = ['' => 'Alle', 'verkaeufer' => 'nur Verkäufer', 'helfer' => 'nur Helfer'];

    protected $table = 'feedback_fragen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'optionen' => 'array',
            'pflicht' => 'boolean',
            'aktiv' => 'boolean',
            'sortierung' => 'integer',
        ];
    }

    public function antworten(): HasMany
    {
        return $this->hasMany(FeedbackAntwort::class);
    }

    public function scopeSortiert(Builder $query): void
    {
        $query->orderBy('sortierung')->orderBy('id');
    }

    /** Aktive Fragen für Verkäufer bzw. Helfer. */
    public function scopeFuer(Builder $query, string $rolle): void
    {
        $query->where('aktiv', true)->where(fn ($q) => $q->whereNull('rolle')->orWhere('rolle', $rolle));
    }

    /** Die Sterne-Frage, deren Schnitt als Gesamtnote in Statistik und Übersicht erscheint: die erste. */
    public static function gesamtnote(): ?self
    {
        return static::query()->where('typ', 'sterne')->orderByDesc('aktiv')->sortiert()->first();
    }

    /** Durchschnitt der Gesamtnote einer Börse (null, solange niemand bewertet hat). */
    public static function schnittFuer(Boerse $boerse): ?float
    {
        $frage = static::gesamtnote();
        $schnitt = $frage?->antworten()->whereHas('feedback', fn ($q) => $q->where('boerse_id', $boerse->id))->avg('zahl');

        return $schnitt === null ? null : (float) $schnitt;
    }
}
