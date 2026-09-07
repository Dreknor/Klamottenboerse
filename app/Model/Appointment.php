<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    public const BEREICH_AUFBAU = 'Aufbau';
    public const BEREICH_BOERSENDIENST = 'Boersendienst';
    public const BEREICH_ABBAU = 'Abbau';

    public const BEREICHE = [
        self::BEREICH_AUFBAU => 'Aufbau',
        self::BEREICH_BOERSENDIENST => 'Börsendienst',
        self::BEREICH_ABBAU => 'Abbau',
    ];

    protected $fillable = ['klamottenboerse_id', 'beschreibung', 'bereich', 'date_start', 'date_end', 'helfer_id', 'erinnerung_versendet_at'];

    protected $casts = [
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'erinnerung_versendet_at' => 'datetime',
    ];

    public function helfer()
    {
        return $this->belongsTo(Helfer::class);
    }


    public function klamottenboerse()
    {
        return $this->belongsTo(Klamottenboerse::class);
    }


}
