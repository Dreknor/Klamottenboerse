<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 13.09.2018
 * Time: 14:21
 */

namespace App\Repositories\Nummern;


use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use Illuminate\Support\Facades\DB;

class VKnummerRepository
{

    public function allLatest(){

        return VKnummer::with('vergeben_an_Interessent', 'reserviert_fuer_Interessent', 'bisherigeVerkaeufer' )
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->get();
    }

    public function freeNummern(){
        return VKnummer::query()
            ->whereNull('vergeben_an')
            ->whereNull('reserviert_fuer')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->get();
    }

    public function vergebeneNummern(Klamottenboerse $klamottenboerse){
        return VKnummer::query()
            ->whereNull('vergeben_an')
            ->whereNull('reserviert_fuer')
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->get();
    }

}