<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 14.04.2016
 * Time: 17:11
 */

namespace App\Repositories\Klamottenboerse;

use App\Models\Klamottenboerse\Klamottenboerse;

class KlamottenboersenRepository
{
    public function latest() {


        $Klamottenboerse= Klamottenboerse::query()
            ->orderBy('id', 'desc')
            ->first();
        $Klamottenboerse->helfer=$Klamottenboerse->helfer()->paginate(10);

        return $Klamottenboerse;
    }
}