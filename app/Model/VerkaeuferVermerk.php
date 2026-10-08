<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Vermerk zur Verkäufer-Reputation (z. B. Kiste nicht gebracht, Termin
 * verpasst, defekte Ware). Die Summe der Punkte innerhalb des konfigurierten
 * Zeitraums entscheidet, ob ein Interessent noch automatisch eine
 * VK-Nummer angeboten bekommt. Arten, Punkte und Schwellen pflegt das
 * Orga-Team unter Settings → Reputation (rein teamintern, für Verkäufer
 * nicht sichtbar).
 */
class VerkaeuferVermerk extends Model
{
    use SoftDeletes;

    public $table = 'verkaeufer_vermerke';

    public const QUELLE_KASSE = 'kasse';
    public const QUELLE_KISTEN = 'kisten';
    public const QUELLE_VERWALTUNG = 'verwaltung';

    protected $fillable = [
        'interessent_id',
        'klamottenboerse_id',
        'vknummer_id',
        'typ',
        'punkte',
        'bemerkung',
        'quelle',
        'erfasst_von',
    ];

    protected $casts = [
        'punkte' => 'integer',
    ];

    /**
     * Aktive (neu erfassbare) Vermerk-Arten, keyed by schluessel.
     */
    public static function typen()
    {
        return VermerkTyp::aktive();
    }

    public static function standardPunkte(string $typ): int
    {
        return (int) optional(VermerkTyp::alle()->get($typ))->punkte;
    }

    public function getTypLabelAttribute(): string
    {
        return optional(VermerkTyp::alle()->get($this->typ))->label ?? $this->typ;
    }

    /**
     * Nur Vermerke innerhalb des Verjährungszeitraums.
     */
    public function scopeWirksam(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subMonths(Einstellung::zahl('reputation_zeitraum_monate')));
    }

    public function interessent()
    {
        return $this->belongsTo(Interessenten::class, 'interessent_id');
    }

    public function vknummer()
    {
        return $this->belongsTo(VKnummer::class, 'vknummer_id');
    }

    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboerse_id');
    }

    public function erfasstVon()
    {
        return $this->belongsTo(User::class, 'erfasst_von');
    }
}
