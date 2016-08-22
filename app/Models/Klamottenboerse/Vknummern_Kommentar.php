<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 21.08.2016
 * Time: 07:23
 */

namespace App\Models\Klamottenboerse;


use Illuminate\Database\Eloquent\Model;

class Vknummern_Kommentar extends Model
{
    public $table = "vknummern_kommentar";

    protected $fillable = array("vknummer","kommentar");

    public function vknummer() {
        return $this->belongsTo(vknummern::class, 'id');
    }
}