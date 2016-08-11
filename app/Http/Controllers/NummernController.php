<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 05.07.2016
 * Time: 05:57
 */

namespace App\Http\Controllers;


use App\Models\Klamottenboerse\Vknummern;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Null_;

class NummernController extends Controller
{
    public function __construct(NummernRepository $nummernRepository, KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->middleware('auth');
        $this->NummernRepository = $nummernRepository;
        $this->Klamottenboerse=$klamottenboersenRepository->getId();
    }

    public function index(){


        $Daten=$this->NummernRepository->all();
        $Interessenten= new InteressentenRepository();
        $Count=array(
            "gesamt" => 0,
            "reserviert" => 0,
            "vergeben" => 0
        );
        $Nummern=array();
        
        foreach ($Daten AS $Nummer) {

            $Count['gesamt']++;

            if ( is_integer($Nummer->reserviert_fuer)){
                $Count['reserviert']++;

                $Nummer->reserviert = $Interessenten->findInteressent($Nummer->reserviert_fuer);
                
                
            }

            if (is_integer($Nummer->vergeben_an) ){
                $Count['vergeben']++;

                $Nummer->vergeben = $Interessenten->findInteressent($Nummer->vergeben_an);
            }

            $Nummern[]=$Nummer;

        }



        return view('vknummern.uebersicht', [
            'Nummern' => $Nummern,
            'Count' => $Count
        ]);
    }
    
    public function newNummer () {
        return view('VKnummern.neueNummer', [
            'Klamottenboerse' => $this->Klamottenboerse
        ]);
    }

    public function store (Request $request) {
        $Nummer=Vknummern::query()->firstOrCreate(['vknummer' => $request->vknummer, 'klamottenboersen_id' => $request->klamottenboersen_id]);
        return view('VKnummern.neueNummer', [
            'Klamottenboerse' => $this->Klamottenboerse
        ]);
        //return redirect()->action('NummernController@index');
    }
    
    public function deleteReservierung ($InteressentenID){
        
        $VKnummer=$this->NummernRepository->deleteReservierung($InteressentenID);

        return redirect()->back();
        
    }

    public function createReservierung($InteressentenID){
        $Interessenten= new InteressentenRepository();
        $Interessent=$Interessenten->findInteressent($InteressentenID);

        $Nummern=$this->NummernRepository->nichtreservierteNummern();

        return view('VKnummern.reserviereNummer', [
            'Interessent' => $Interessent,
            'Nummern' => $Nummern
        ]);
    }

    public function storeReservierung($InteressentenID, request $request){

        $Nummer=Vknummern::query()
                    ->where('id', $request->vknummer)
                    ->update(['reserviert_fuer' => $InteressentenID]);

        return redirect(url('Interessent/'.$InteressentenID));


    }
    
    public function storeVergabe(Request $request){
        $Nummer=$this->NummernRepository->getVKNummer($request->input('NummernID'));
        
        if ($Nummer->vergeben_an == "" and ($Nummer->reserviert_fuer == "" OR $Nummer->reserviert_fuer == $request->input('InteressentenID'))){

            return redirect()->back()->with(['Meldung' => 'Nummer wurde vergeben', 'type' => 'success']);
            
        } else {
            return redirect()->back()->with(['Meldung'=> 'Nummer konnte nicht vergeben werden', 'type' => 'danger']);
        }

    }
}