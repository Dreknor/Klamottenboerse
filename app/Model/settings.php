<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class settings extends Model
{
    //
    public $table ="settings";

    public $fillable = ["name",	"kinderhaus",	"datum",	"provision"];

    protected $dates = ["datum"];

}
