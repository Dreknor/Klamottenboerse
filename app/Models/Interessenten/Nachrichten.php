<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 26.03.2016
 * Time: 22:21
 */

namespace App\Models\Interessenten;


use Illuminate\Database\Eloquent\Model;

class Nachrichten extends Model
{
    public $table = "nachrichten";

    protected $fillable = array('interessent_id', 'betreff', 'nachricht', 'pfad' );

    public function Interessent () {
        return $this->belongsTo(Interessenten::class, 'interessent_id');
    }

}