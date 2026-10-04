<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Ordner der Team-Ablage. Dateien liegen privat (Disk "local") und werden nur über das Backend ausgeliefert. */
class Ordner extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const MAX_KB = 20 * 1024;

    protected $table = 'ordner';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['fuer_helfer' => 'boolean'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('dateien')->useDisk('local');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('vorschau')
            ->fit(Fit::Max, 480, 480)
            ->performOnCollections('dateien')
            ->nonQueued();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function kinder(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('name');
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    /** @return Collection<int, Ordner> Pfad von oben nach unten (für die Brotkrumen-Navigation) */
    public function pfad(): Collection
    {
        $pfad = collect([$this]);
        $aktuell = $this;
        while ($aktuell->parent_id && ($aktuell = self::find($aktuell->parent_id))) {
            $pfad->prepend($aktuell);
        }

        return $pfad;
    }

    /** Für Helfer sichtbar, wenn der Ordner selbst oder ein übergeordneter freigegeben ist. */
    public function sichtbarFuerHelfer(): bool
    {
        return $this->pfad()->contains(fn (Ordner $o) => $o->fuer_helfer);
    }
}
