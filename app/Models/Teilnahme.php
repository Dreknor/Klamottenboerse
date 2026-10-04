<?php

namespace App\Models;

use App\Enums\TeilnahmeStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teilnahme extends Model
{
    use HasFactory;

    protected $table = 'teilnahmen';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status' => TeilnahmeStatus::class,
            'ist_kinderhaus' => 'boolean',
            'spendenfrei' => 'boolean',
            'angebot_bis' => 'datetime',
            'angemeldet_at' => 'datetime',
            'zugeteilt_at' => 'datetime',
            'angeliefert_at' => 'datetime',
            'abgesagt_at' => 'datetime',
        ];
    }

    public function boerse(): BelongsTo
    {
        return $this->belongsTo(Boerse::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function artikel(): HasMany
    {
        return $this->hasMany(Artikel::class)->orderBy('laufnummer');
    }

    public function kisten(): HasMany
    {
        return $this->hasMany(Kiste::class)->orderBy('kistennummer');
    }

    public function bonpositionen(): HasMany
    {
        return $this->hasMany(Bonposition::class);
    }

    public function abrechnung(): HasOne
    {
        return $this->hasOne(Abrechnung::class);
    }

    public function scopeMitNummer(Builder $query): void
    {
        $query->whereIn('status', TeilnahmeStatus::mitNummer());
    }

    public function anzeigeName(): string
    {
        return $this->ist_kinderhaus ? 'Kinderhaus' : ($this->person?->name ?? '–');
    }

    public function hatNummer(): bool
    {
        return $this->nummer !== null && in_array($this->status, TeilnahmeStatus::mitNummer(), true);
    }

    /** Gültige (nicht stornierte) Verkäufe dieser Teilnahme. */
    public function gueltigePositionen(): HasMany
    {
        return $this->bonpositionen()
            ->whereNull('bonpositionen.storniert_at')
            ->whereHas('bon', fn ($q) => $q->whereNull('storniert_at'));
    }
}
