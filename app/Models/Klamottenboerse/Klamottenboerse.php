<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 14.04.2016
 * Time: 17:12
 */

namespace App\Models\Klamottenboerse;


use Illuminate\Database\Eloquent\Model;

class Klamottenboerse extends Model
{
    public $table = "klamottenboerse";

    protected $fillable = array('datum', 'anmeldung', 'anmeldungKinderhaus', 'anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis','maxTeile');
    protected $dates = ['created_at', 'updated_at', 'datum', 'anmeldung', 'anmeldungKinderhaus'];
    protected $times = ['anlieferung_von', 'anlieferung_bis', 'abholung_von', 'abholung_bis'];

    public function helfer(){
        return $this->hasMany(Helfer::class, 'klamottenboerse_id')
            ->orderBy('bereich', 'desc');

    }

    public function vknummern(){
        return $this->hasMany(Vknummern::class, 'klamottenboersen_id')
            ->orderBy('vknummer');

    }


}