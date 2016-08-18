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
use App\Repositories\Nachrichten\NachrichtenRepository;
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

    public function deleteVergabe ($NummernID) {

        $Nummer=Vknummern::query()
            ->Where('id',$NummernID)
            ->update(['vergeben_an' => NULL]);

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

    public function storeNummer($InteressentenID, $NummernID){


        $Nummer=Vknummern::query()
            ->Where('id',$NummernID)
            ->Where('vergeben_an', NULL)
            ->update(['vergeben_an' => $InteressentenID]);

        if ($Nummer==1){
            $NachrichtenRepository=new NachrichtenRepository();
            
            $Nachricht=[
                'betreff' => 'Verkäufernummer zur Klamottenbörse',
                'text' => 'Sehr geehrte ',
            ];
        }
        return $Nummer;
    }

    public function countVerkaeufer(){

        return Vknummern::query()
                    ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
                    ->whereNotNull('vergeben_an')
                    ->count();
    }

    public function getVerkaeufer(){

        $Interessenten =new InteressentenRepository();

        $vergebeneNummern = Vknummern::query()
                                ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
                                ->whereNotNull('vergeben_an')
                                ->get();

        foreach ($vergebeneNummern AS $Nummer){
            $Interessent[]=$Interessenten->findInteressent($Nummer->vergeben_an);
        }

        return $Interessent;
    }

    public function getVerkaeufer2(){


        $vergebeneNummern = Vknummern::query()
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->whereNotNull('vergeben_an')
            ->leftJoin('interessenten', 'vknummern.vergeben_an', '=', 'interessenten.id')
            ->select('interessenten.*', 'vknummern.vknummer')

            ->get();


        return $vergebeneNummern;
    }

    public function haeufigsteNummer($InteressentenID){

        return $HaeufigsteNummer=Vknummern::query()
                            ->where('vergeben_an', $InteressentenID)
                            ->select(DB::raw('*, count(vknummer) as Anzahl'))
                            ->groupBy('vknummer')
                            ->orderBy('Anzahl', 'DESC')
                            ->take(2)
                            ->get();



    }

    public function letzteVKNummer($InteressentenID) {
        return Vknummern::query()
                    ->where('vergeben_an', $InteressentenID)
                    ->orderBY('klamottenboersen_id', 'DESC')
                    ->take(1)
                    ->first();
    }

    public function getVKNummer_vknummer($vknummer){
        $Nummer= Vknummern::query()
            ->where('vknummer', $vknummer)
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->first();

        return $Nummer;
    }

    public function NummerPruefen($vknummer){
        $Nummer= Vknummern::query()
            ->where('vknummer', $vknummer)
            ->Where('reserviert_fuer', NULL)
            ->Where('vergeben_an', NULL)
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))
            ->first();

        return $Nummer;
    }

    public function getNummernMitInteressenten(){


        $Nummern = Vknummern::query()
            ->where('klamottenboersen_id', DB::raw("(select max(`id`) from klamottenboerse)"))

            ->leftJoin('interessenten', 'vknummern.vergeben_an', '=', 'interessenten.id')
            ->select('interessenten.*', 'vknummern.vknummer')
            ->orderBy('vknummer')
            ->get();


        return $Nummern;
    }

   
}