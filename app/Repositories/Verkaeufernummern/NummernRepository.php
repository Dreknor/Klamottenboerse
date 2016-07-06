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

        $Klamottenboerse=new KlamottenboersenRepository;
        $Klamottenboerse_id=$Klamottenboerse->getId();
        
           $Nummern = Vknummern::query()
                 ->where('klamottenboersen_id', '=', $Klamottenboerse_id)
                    ->leftjoin('interessenten', 'interessenten.id', '=', 'vknummern.reserviert_fuer')
                    ->select ('vknummern.*', 'interessenten.vorname', 'interessenten.nachname')
                 ->get();
            
        return $Nummern;

    }
    
    
}