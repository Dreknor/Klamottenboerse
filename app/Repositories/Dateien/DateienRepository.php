<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 03.04.2016
 * Time: 08:27
 */

namespace App\Repositories\Dateien;

use App\Models\Dateien\Dateien;

class DateienRepository
{

    public function all(){
        return Dateien::query()->get();

    }

    public function findDatei ($id) {
        $Datei=Dateien::query()->findOrFail($id);
        return $Datei;
    }
}