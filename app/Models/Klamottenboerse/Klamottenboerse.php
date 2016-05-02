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

    protected $fillable = array('datum', 'anmeldung', 'anmeldungKinderhaus');
    protected $dates = ['created_at', 'updated_at', 'datum', 'anmeldung', 'anmeldungKinderhaus'];

    public function helfer(){
        return $this->hasMany(Helfer::class, 'klamottenboerse_id')
            ->orderBy('bereich', 'desc');

    }


}