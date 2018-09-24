<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use App\Model\Interessenten;


class VKnummer extends Model
{
    public $table = "vknummern";

    protected $fillable = array("vknummer","klamottenboersen_id", "reserviert_fuer", "vergeben_an", 'umsatz');
    protected $visible = array("vknummer","klamottenboersen_id", "reserviert_fuer", "vergeben_an", 'umsatz');

    public function reserviert_fuer_Interessent() {
        return $this->belongsTo( 'App\Model\Interessenten','reserviert_fuer');
    }

    public function vergeben_an_Interessent() {
        return $this->belongsTo(Interessenten::class, 'vergeben_an', 'id', 'vergeben_an');
    }

    public function Klamottenboerse () {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboersen_id');
    }

    public function bisherigeVerkaeufer(){
        return $this->hasMany(VKnummer::class, 'vknummer', 'vknummer')->whereNotNull('vergeben_an')->orderBy('klamottenboersen_id', 'DESC')->with('vergeben_an_Interessent');    }

}
