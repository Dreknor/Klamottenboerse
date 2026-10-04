<?php

namespace App\Models;

use App\Enums\BoerseStatus;
use App\Enums\TeilnahmeStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boerse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'boersen';

    protected $guarded = ['id'];

    /** Felder mit Datum/Uhrzeit, die beim Kopieren einer Börse verschoben werden. */
    public const TERMINFELDER = [
        'anmeldung_kinderhaus_ab', 'anmeldung_ab', 'anlieferung_beginn', 'anlieferung_ende',
        'verkauf_beginn', 'verkauf_ende', 'abholung_beginn', 'abholung_ende',
    ];

    protected function casts(): array
    {
        return [
            'verkaufstag' => 'date',
            'status' => BoerseStatus::class,
            'anmeldung_kinderhaus_ab' => 'datetime',
            'anmeldung_ab' => 'datetime',
            'anlieferung_beginn' => 'datetime',
            'anlieferung_ende' => 'datetime',
            'verkauf_beginn' => 'datetime',
            'verkauf_ende' => 'datetime',
            'abholung_beginn' => 'datetime',
            'abholung_ende' => 'datetime',
            'ergebnis_freigegeben' => 'boolean',
            'live_erloes_freigegeben' => 'boolean',
        ];
    }

    public function ort(): BelongsTo
    {
        return $this->belongsTo(Ort::class);
    }

    public function teilnahmen(): HasMany
    {
        return $this->hasMany(Teilnahme::class);
    }

    public function kinderhausTeilnahme(): HasOne
    {
        return $this->hasOne(Teilnahme::class)->where('ist_kinderhaus', true);
    }

    public function schichten(): HasMany
    {
        return $this->hasMany(Schicht::class)->orderBy('beginn');
    }

    public function aufgaben(): HasMany
    {
        return $this->hasMany(Aufgabe::class);
    }

    public function mailplan(): HasMany
    {
        return $this->hasMany(MailplanEintrag::class);
    }

    public function bons(): HasMany
    {
        return $this->hasMany(Bon::class);
    }

    public function sonstigeEinnahmen(): HasMany
    {
        return $this->hasMany(SonstigeEinnahme::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function scopeOffen(Builder $query): void
    {
        $query->where('status', '!=', BoerseStatus::Abgeschlossen->value);
    }

    /** Die nächste noch nicht abgeschlossene Börse, sonst die zuletzt stattgefundene. */
    public static function aktuelle(): ?self
    {
        return static::query()->offen()->where('verkaufstag', '>=', today()->subDays(14))
            ->orderBy('verkaufstag')->first()
            ?? static::query()->orderByDesc('verkaufstag')->first();
    }

    /** Phase, abgeleitet aus den Terminen (außer bei manuell abgeschlossenen Börsen). */
    public function phase(?CarbonInterface $jetzt = null): BoerseStatus
    {
        $jetzt ??= now();

        if ($this->status === BoerseStatus::Abgeschlossen) {
            return BoerseStatus::Abgeschlossen;
        }

        $anmeldestart = $this->anmeldung_kinderhaus_ab ?? $this->anmeldung_ab;

        return match (true) {
            $jetzt->isAfter($this->verkaufstag->copy()->endOfDay()) => BoerseStatus::Abrechnung,
            $jetzt->isSameDay($this->verkaufstag) => ($this->verkauf_ende && $jetzt->isAfter($this->verkauf_ende))
                ? BoerseStatus::Abrechnung : BoerseStatus::Verkauf,
            $this->anlieferung_beginn && $jetzt->isAfter($this->anlieferung_beginn->copy()->subDays(21)) => BoerseStatus::Vorbereitung,
            $anmeldestart && $jetzt->isAfter($anmeldestart) => BoerseStatus::Anmeldung,
            default => BoerseStatus::Planung,
        };
    }

    public function anmeldungOffenFuer(Person $person, ?CarbonInterface $jetzt = null): bool
    {
        $jetzt ??= now();
        $start = $person->kinderhaus_bezug?->hatVorlauf() && $this->anmeldung_kinderhaus_ab
            ? $this->anmeldung_kinderhaus_ab
            : $this->anmeldung_ab;

        return $start !== null && $jetzt->isAfter($start) && $jetzt->isBefore($this->verkaufstag);
    }

    /**
     * Die 100er-Blöcke des Nummernbereichs, z. B. [200 => [200, 299], 300 => [300, 399], ...].
     *
     * @return array<int, array{0:int, 1:int}>
     */
    public function bloecke(): array
    {
        $bloecke = [];
        for ($start = $this->nummer_von; $start <= $this->nummer_bis; $start += $this->blockgroesse) {
            $bloecke[$start] = [$start, min($start + $this->blockgroesse - 1, $this->nummer_bis)];
        }

        return $bloecke;
    }

    public function blockVon(int $nummer): ?int
    {
        foreach ($this->bloecke() as $start => [$von, $bis]) {
            if ($nummer >= $von && $nummer <= $bis) {
                return $start;
            }
        }

        return null;
    }

    /** Zielwert je Block = Kapazität ÷ Anzahl Blöcke (aufgerundet). */
    public function zielProBlock(): int
    {
        return (int) ceil($this->kapazitaet / max(1, count($this->bloecke())));
    }

    /** Anzahl belegter Verkäufernummern (ohne Kinderhaus). */
    public function belegteNummern(): int
    {
        return $this->teilnahmen()->mitNummer()->where('ist_kinderhaus', false)->count();
    }

    public function freiePlaetze(): int
    {
        return max(0, $this->kapazitaet - $this->belegteNummern());
    }

    public function istVoll(): bool
    {
        return $this->freiePlaetze() === 0;
    }

    public function verkaeufer(): HasMany
    {
        return $this->teilnahmen()->whereIn('status', [
            TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Angeliefert,
            TeilnahmeStatus::Abgerechnet, TeilnahmeStatus::Ausgezahlt,
        ]);
    }
}
