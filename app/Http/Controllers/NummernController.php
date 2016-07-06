<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 05.07.2016
 * Time: 05:57
 */

namespace App\Http\Controllers;


use App\Repositories\Verkaeufernummern\NummernRepository;

class NummernController extends Controller
{
    public function __construct(NummernRepository $nummernRepository)
    {
        $this->middleware('auth');
        $this->NummernRepository = $nummernRepository;
    }

    public function index(){

        $Daten=$this->NummernRepository->all();
        $Count=array(
            "gesamt" => 0,
            "reserviert" => 0,
            "vergeben" => 0
        );

        foreach ($Daten AS $Nummer) {

            $Count['gesamt']++;

            if ($Nummer->reserviert_fuer !=""){
                $Count['reserviert']++;
            }

            if ($Nummer->vergeben_an !=""){
                $Count['vergeben']++;
            }

        }



        return view('vknummern.uebersicht', [
            'Nummern' => $Daten,
            'Count' => $Count
        ]);
    }
}