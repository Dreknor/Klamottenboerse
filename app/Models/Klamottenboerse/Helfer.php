<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 26.04.2016
 * Time: 17:53
 */

namespace App\Models\Klamottenboerse;


use Illuminate\Database\Eloquent\Model;

class Helfer extends Model
{
    public $table = "helfer";

    protected $fillable = array('klamottenboerse_id', 'name', 'telefon', 'mail', 'bereich' );

    public function Klamottenboerse () {
        return $this->belongsTo(Klamottenboerse::class, 'klamottenboerse_id');
    }

}