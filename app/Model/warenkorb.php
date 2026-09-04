<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class warenkorb extends Model
{

    public $table = "warenkorb";
    public $fillable = ["user_id", "vknummer", "artikelnummer", "betrag"];


}
