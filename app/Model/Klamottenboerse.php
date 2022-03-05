<?php

namespace App\Model;

use App\Model\VKnummer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Klamottenboerse extends Model
{
    public $table = 'klamottenboerse';

    protected $fillable = ['datum', 'anmeldung', 'anmeldungKinderhaus', 'anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis', 'maxTeile', 'sendInvitation', 'sendErinnerung'];

    protected $dates = ['created_at', 'updated_at', 'datum', 'anmeldung', 'anmeldungKinderhaus'];

    protected $times = ['anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis'];

    protected $casts = [
      'boolean' => 'sendInvitation'
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
}
