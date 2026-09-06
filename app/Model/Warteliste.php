<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 06:57
 */

namespace App\Model;

use App\Model\Interessenten;
use Illuminate\Database\Eloquent\Model;

class Warteliste extends Model
{
    public $table = 'warteliste';

    protected $fillable = [
        'interessenten_id',
        'angebotene_vknummer_id',
        'angebot_versendet_at',
        'angebot_ablauf_at',
        'bestaetigt_at',
        'token',
        'uebersprungene_vknummern',
    ];

    protected $casts = [
        'angebot_versendet_at' => 'datetime',
        'angebot_ablauf_at' => 'datetime',
        'bestaetigt_at' => 'datetime',
        'uebersprungene_vknummern' => 'array',
    ];

    public function Interessent()
    {
        return $this->belongsTo(Interessenten::class, 'interessenten_id');
    }

    public function angeboteneVknummer()
    {
        return $this->belongsTo(VKnummer::class, 'angebotene_vknummer_id');
    }

    /**
     * Es liegt ein noch gültiges (nicht abgelaufenes, unbestätigtes) Angebot vor.
     */
    public function hatAktivesAngebot(): bool
    {
        return $this->angebotene_vknummer_id !== null
            && $this->bestaetigt_at === null
            && $this->angebot_ablauf_at !== null
            && $this->angebot_ablauf_at->isFuture();
    }

    /**
     * Das Zeitfenster zur Bestätigung ist abgelaufen, ohne dass reagiert wurde.
     */
    public function istAngebotAbgelaufen(): bool
    {
        return $this->angebotene_vknummer_id !== null
            && $this->bestaetigt_at === null
            && $this->angebot_ablauf_at !== null
            && $this->angebot_ablauf_at->isPast();
    }
}
