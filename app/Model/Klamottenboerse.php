<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Model\VKnummer;

class Klamottenboerse extends Model
{
    public $table = "klamottenboerse";

    protected $fillable = array('datum', 'anmeldung', 'anmeldungKinderhaus', 'anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis','maxTeile');
    protected $dates = ['created_at', 'updated_at', 'datum', 'anmeldung', 'anmeldungKinderhaus'];
    protected $times = ['anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis'];

    public function getAnlieferungvonAttribute($value){
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAnlieferungbisAttribute($value){
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAbholungvonAttribute($value){
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function getAbholungbisAttribute($value){
        return Carbon::createFromTimeString($value)->format('H:i');
    }

    public function vknummern(){
        return $this->hasMany('App\Model\VKnummer', 'klamottenboersen_id', 'id');
    }
}
