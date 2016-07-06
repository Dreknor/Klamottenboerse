<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 03.07.2016
 * Time: 09:31
 */

namespace App\Models\Klamottenboerse;


use App\Models\Interessenten\Interessenten;
use Illuminate\Database\Eloquent\Model;

class Vknummern extends Model
{
    public $table = "vknummern";

    protected $fillable = array("vknummer","klamottenboersen_id", "reserviert_fuer");

    public function reserviert_fuer () {
        return $this->belongsTo(Interessenten::class, 'reserviert_fuer');
    }

    public function vergeben_an () {
        return $this->belongsTo(Interessenten::class, 'vergeben_an');
    }

    public function Klamottenboerse () {
        return $this->belongsTo(Interessenten::class, 'klamottenboersen_id');
    }

}