<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 02.04.2016
 * Time: 23:23
 */

namespace App\Models\Dateien;


use Illuminate\Database\Eloquent\Model;

class Dateien extends Model
{
    public $table = "dateien";

    protected $fillable = array('dateiname', 'dateibeschreibung', 'pfad', 'mime');
}