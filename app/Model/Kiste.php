<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Digitales Check-in / Check-out der Warenkisten eines Verkäufers.
 * Minimiert Verluste, da jede Kiste einzeln erfasst und beim Check-out
 * gegen das Check-in abgeglichen wird (optional per QR-Code-Scan).
 */
class Kiste extends Model
{
    use SoftDeletes;

    public $table = 'kisten';

    public const STATUS_ABGEGEBEN = 'abgegeben';
    public const STATUS_ABGEHOLT = 'abgeholt';

    protected $fillable = [
        'klamottenboerse_id',
        'vknummer_id',
        'kistennummer',
        'qr_token',
        'status',
        'abgegeben_at',
        'abgegeben_von',
        'abgeholt_at',
        'abgeholt_von',
        'bemerkung',
    ];

    protected $casts = [
        'abgegeben_at' => 'datetime',
        'abgeholt_at' => 'datetime',
    ];

    public function vknummer()
    {
        return $this->belongsTo(VKnummer::class, 'vknummer_id');
    }

    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboerse_id');
    }

    public function abgegebenVon()
    {
        return $this->belongsTo(User::class, 'abgegeben_von');
    }

    public function abgeholtVon()
    {
        return $this->belongsTo(User::class, 'abgeholt_von');
    }

    public function istAbgeholt(): bool
    {
        return $this->status === self::STATUS_ABGEHOLT;
    }

    /**
     * Nächste freie, fortlaufende Kistennummer für die angegebene VK-Nummer.
     */
    public static function naechsteKistennummer(int $vknummerId): int
    {
        return (int) (self::withTrashed()->where('vknummer_id', $vknummerId)->max('kistennummer')) + 1;
    }

    public static function generiereQrToken(): string
    {
        do {
            $token = Str::random(32);
        } while (self::withTrashed()->where('qr_token', $token)->exists());

        return $token;
    }
}
