<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 05.07.2016
 * Time: 05:58
 */

namespace App\Repositories\Verkaeufernummern;


use App\Models\Klamottenboerse\Klamottenboerse;
use App\Models\Klamottenboerse\Vknummern;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;

class NummernRepository
{
    public function all(){

        $Klamottenboerse=KlamottenboersenRepository::class;
        $Latest=$Klamottenboerse;

        return Vknummern::query()
            ->where('klamottenboerse_id', '=', $Latest)
            ->first();
    }
}