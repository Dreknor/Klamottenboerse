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
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Support\Facades\DB;

class NummernRepository
{
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->klamottenboersenRepository=$klamottenboersenRepository;
    }

    public function all(){

        $Klamottenboerse_id= $this->klamottenboersenRepository->getId();
                
           $Nummern = Vknummern::query()
                 ->where('klamottenboersen_id', '=', $Klamottenboerse_id)
                    ->orderBy('vknummer')
                 ->get();

            
        return $Nummern;

    }

    public function getVKNummer ($id) {

        $Nummer=Vknummern::query()->findOrFail($id);

        return $Nummer;

    }

    public function deleteReservierung ($InteressentenID) {

        $Nummer=Vknummern::query()
                    ->Where('reserviert_fuer',$InteressentenID)
                    ->Where('klamottenboersen_id', $this->klamottenboersenRepository->getId())
                        ->update(['reserviert_fuer' => NULL]);

        return $Nummer;

    }

    public function nichtreservierteNummern()
    {

        $Nummmern = Vknummern::query()
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->whereNull('reserviert_fuer')
            ->whereNull('vergeben_an')
            ->orderBy('vknummer')
            ->get();

        return $Nummmern;
    }


    
}