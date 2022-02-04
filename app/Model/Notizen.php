<?php

namespace App\Model;

use App\Model\Interessenten;
use Illuminate\Database\Eloquent\Model;

class Notizen extends Model
{
    protected $table = "notizen";

    protected $fillable = array('interessenten_id', 'notiz');

    public function Interessent () {
        return $this->belongsTo(Interessenten::class, 'interessenten_id');
    }
}
