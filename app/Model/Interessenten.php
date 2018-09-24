<?php

namespace App\Model;

use App\Repositories\Mails\ImapRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Interessenten extends Model
{

    public $table = "interessenten";

    protected $fillable = array('vorname', 'nachname', 'mail', 'telefon', 'anrede', 'mitarbeiter', 'kinderhaus', 'handy' );


    public function getKinderhausAttribute($value)
    {
        if ($value == 1){
            return "ja";
        }
            return "nein";
    }

    public function getMitarbeiterAttribute($value)
    {
        if ($value == 1){
            return "ja";
        }
        return "nein";
    }

    public function vknummer_reserviert(){
        return $this->hasOne(VKnummer::class, 'reserviert_fuer')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->orderBy('klamottenboersen_id', 'desc');
    }

    public function vknummern_vergeben(){
        return $this->hasOne(VKnummer::class, 'vergeben_an')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->orderBy('klamottenboersen_id', 'desc');

    }

    public function bisherige_vknummen(){
        return $this->hasMany(VKnummer::class, 'vergeben_an')
            ->orderBy('klamottenboersen_id', 'desc');

    }



}
