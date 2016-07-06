<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.03.2016
 * Time: 21:37
 */

namespace App\Models\Interessenten;


use App\Models\Klamottenboerse\Vknummern;
use Illuminate\Database\Eloquent\Model;

class Interessenten extends Model
{
    public $table = "interessenten";

    protected $fillable = array('vorname', 'nachname', 'mail', 'telefon', 'plz', 'ort', 'straße', 'hausnummer', 'anrede', 'mitarbeiter', 'kinderhaus' );

    public function nachrichten(){
        return $this->hasMany(Nachrichten::class, 'interessent_id')
            ->orderBy('created_at', 'desc');

    }

    public function vknummern_reserviert(){
        return $this->hasOne(Vknummern::class, 'reserviert_fuer')
            ->orderBy('klamottenboersen_id', 'desc');

    }

    public function nachrichtenPagination(){
            return $this->nachrichten()->paginate(10);
    }
}

