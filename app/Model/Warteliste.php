<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 06:57
 */

namespace App\Model;

use App\Model\Interessenten;
use Illuminate\Database\Eloquent\Model;

class Warteliste extends Model
{
    public $table = 'warteliste';

    protected $fillable = ['interessenten_id'];

    public function Interessent()
    {
        return $this->belongsTo(Interessenten::class, 'interessenten_id');
    }
}
