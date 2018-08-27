<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.03.2016
 * Time: 21:37
 */

namespace App\Models\Interessenten;


use App\Models\Klamottenboerse\Helfer;
use App\Models\Klamottenboerse\Vknummern;
use App\Models\Klamottenboerse\Warteliste;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Interessenten extends Model
{
    public $table = "interessenten";

    protected $fillable = array('vorname', 'nachname', 'mail', 'telefon', 'plz', 'ort', 'straße', 'hausnummer', 'anrede', 'mitarbeiter', 'kinderhaus', 'handy' );

    public function nachrichten(){
        return $this->hasMany(Nachrichten::class, 'interessent_id')
            ->orderBy('created_at', 'desc');

    }

    public function vknummern_reserviert(){
        return $this->hasOne(Vknummern::class, 'reserviert_fuer')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->orderBy('klamottenboersen_id', 'desc');
    }

    public function vknummern_vergeben(){
        return $this->hasOne(Vknummern::class, 'vergeben_an')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->orderBy('klamottenboersen_id', 'desc');

    }

    public function Warteliste(){
        return $this->hasOne(Warteliste::class, 'Interessenten_id');
    }

    public function nachrichtenPagination(){
            return $this->nachrichten()->paginate(10);
    }

    public function helfer(){
        return $this->hasOne(Helfer::class, 'name', 'nachname')
            ->where('klamottenboerse_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->orderBy('klamottenboerse_id', 'desc');
    }

    public function notiz (){
        return $this->hasOne(Notizen::class, 'interessenten_id');
    }
}

