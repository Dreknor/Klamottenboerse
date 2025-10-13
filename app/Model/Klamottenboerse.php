<?php

namespace App\Model;

use App\Model\VKnummer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Klamottenboerse extends Model
{
    use SoftDeletes;
    public $table = 'klamottenboerse';

    protected $fillable = ['belehrung', 'datum', 'anmeldung', 'anmeldungKinderhaus', 'anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis', 'maxTeile', 'sendInvitation', 'sendErinnerung', 'ort', 'adresse', 'ergebnis_freigabe'];

   // protected $dates = ['created_at', 'updated_at', 'datum', 'anmeldung', 'anmeldungKinderhaus'];

    protected $times = ['anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis'];

    protected $casts = [
     'sendInvitation' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'datum'  => 'datetime',
        'anmeldung'  => 'datetime',
        'anmeldungKinderhaus' => 'datetime',
        'ergebnis_freigabe' => 'boolean',
    ];

    public function getAnlieferungvonAttribute($value)
    {
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAnlieferungbisAttribute($value)
    {
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAbholungvonAttribute($value)
    {
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAbholungbisAttribute($value)
    {
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function vknummern()
    {
        return $this->hasMany(\App\Model\VKnummer::class, 'klamottenboersen_id', 'id');
    }

    public function vknummern_vergeben()
    {
        return $this->hasMany(\App\Model\VKnummer::class, 'klamottenboersen_id', 'id')->where('vergeben_an', '!=', '');
    }

    public function verkaeufe()
    {
        return $this->hasMany(\App\Model\verkaeufe::class, 'klamottenboerse_id', 'id');
    }

    public function verkaufteArtikel()
    {
        return $this->hasMany(verkaufteartikel::class, 'klamottenboerse_id', 'id')->withoutGlobalScopes();
    }
}
