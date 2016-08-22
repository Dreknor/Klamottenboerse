<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.08.2016
 * Time: 21:15
 */

namespace App\Models\Mailvorlagen;


use Illuminate\Database\Eloquent\Model;

class mailvorlagen extends Model
{
    public $table ="mailvorlagen";
    
    protected $fillable = ['name', 'betreff', 'text'];


}