<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.08.2016
 * Time: 21:09
 */

namespace App\Repositories\Mailvorlagen;


use App\Models\Mailvorlagen\Mailvorlagen;

class VorlagenRepository
{
    public function alle (){
        return Mailvorlagen::query()->get();
    }

    public function find($id){
        return Mailvorlagen::query()->findOrFail($id);
    }
}