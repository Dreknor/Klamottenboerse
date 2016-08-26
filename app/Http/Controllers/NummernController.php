<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 05.07.2016
 * Time: 05:57
 */

namespace App\Http\Controllers;


use App\Models\Klamottenboerse\Vknummern;
use App\Models\Klamottenboerse\Vknummern_Kommentar;
use App\Repositories\Dateien\DateienRepository;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Nachrichten\NachrichtenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

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

        $meisteNummer=$this->NummernRepository->haeufigsteNummer('1');

        return view('vknummern.uebersicht', [
            'Nummern' => $Nummern,
            'Count' => $Count,
            'meisteNummer' => $meisteNummer
        ]);
    }
    
    public function newNummer () {
        return view('VKnummern.neueNummer', [
            'Klamottenboerse' => $this->Klamottenboerse
        ]);
    }

    public function store (Request $request) {
        $Nummer=Vknummern::query()->firstOrCreate(['vknummer' => $request->vknummer, 'klamottenboersen_id' => $request->klamottenboersen_id]);
        /*return view('VKnummern.neueNummer', [
            'Klamottenboerse' => $this->Klamottenboerse
        ]);*/
        return redirect()->back()->with(['Meldung'=> 'Nummer '.$request->vknummer.' angelegt', 'type' => 'success']);
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

        $Nummer=$this->NummernRepository->storeNummer($request->input('InteressentenID'), $request->input('NummernID'));
        $Interessent= InteressentenRepository::findInteressent($request->input('InteressentenID'));
       if ($Nummer==1){

           $Interessent= InteressentenRepository::findInteressent($request->input('InteressentenID'));
           $VKnummer=$this->NummernRepository->getVKNummer($request->input('NummernID'));
           $DateienRepository = new DateienRepository();
           $VerkaeuferInfos=$DateienRepository->findDateiName('Verkäuferinfos');

           $text = View::make('emails.vergabeVKNummer', [
               'Interessent'=> $Interessent,
               'VKNummer' => $VKnummer
           ]);
           
           $Nachricht=[
               'betreff' => 'Verkäufernummer Klamottenbörse',
               'nachricht'   => $text,
               'anhang' => $VerkaeuferInfos->pfad,
               'view'   => 'emails.blank'
           ];

           $Nachrichten=new NachrichtenRepository;
           $Email = $Nachrichten->send($Interessent, $Nachricht);

           return redirect(url('Interessent/'.$Interessent->id))->with(['Meldung' => 'Nummer wurde vergeben', 'type' => 'success']);

        } else {

           return redirect(url('Interessent/'.$Interessent->id))->with(['Meldung'=> 'Nummer konnte nicht vergeben werden', 'type' => 'danger']);


        }

    }
    
    public function vergabeLoeschen(Request $request) {
        
        $Nummer=$this->NummernRepository->deleteVergabe($request->input('NummernID'));

        if ($Nummer==1){

            $Interessent= InteressentenRepository::findInteressent($request->input('InteressentenID'));
            $VKnummer=$this->NummernRepository->getVKNummer($request->input('NummernID'));

            $text = View::make('emails.LoescheVKNummer', [
                'Interessent'=> $Interessent,
                'VKNummer' => $VKnummer
            ]);

            $Nachricht=[
                'betreff' => 'Verkäufernummer Klamottenbörse',
                'nachricht'   => $text,
                'anhang' => '',
                'view'   => 'emails.blank'
            ];

            $Nachrichten=new NachrichtenRepository;
            $Email = $Nachrichten->send($Interessent, $Nachricht);

            return redirect()->back()->with(['Meldung' => 'Vergabe wurde aufgehoben', 'type' => 'success']);

        } else {
           return redirect()->back()->with(['Meldung'=> 'Vergaberücknahme gescheitert', 'type' => 'danger']);

        }
    }

    public function NummerLoeschen (Request $request) {

        $Nummer=$this->NummernRepository->getVKNummer($request->input('id'));

        if ($Nummer->vergeben_an != NULL) {
            $Interessent= InteressentenRepository::findInteressent($Nummer->vergeben_an);
            $VKnummer=$this->NummernRepository->getVKNummer($request->input('id'));

            $text = View::make('emails.LoescheVKNummer', [
                'Interessent'=> $Interessent,
                'VKNummer' => $VKnummer
            ]);

            $Nachricht=[
                'betreff' => 'Verkäufernummer Klamottenbörse',
                'nachricht'   => $text,
                'anhang' => '',
                'view'   => 'emails.blank'
            ];

            $Nachrichten=new NachrichtenRepository;
            $Email = $Nachrichten->send($Interessent, $Nachricht);

        }

        $Nummer->delete();

        return redirect() -> back() -> with(['Meldung' => 'Die Nummer wurde gelöscht.', 'type' => 'success']);
    }
    
    public function Nummernvergabe ($InteressentenID){
        $Interessent = InteressentenRepository::findInteressent($InteressentenID);
        $Nummern=$this->NummernRepository->nichtreservierteNummern();

        return view('vknummern.nummernvergabe', [
            'Interessent' => $Interessent,
            'Nummern' => $Nummern
        ]);
    }

   public function storeKommentar (Request $request){
       $Kommentar = Vknummern_Kommentar::firstOrNew(['vknummer' => $request->input('vknummer')]);
       $Kommentar->kommentar = $request->input('kommentar');
       $Kommentar->save();


       return redirect()->back()->with(['Meldung' => 'Kommentar gespeichert.', 'type' => 'success']);
   }

    public function KommentarLoeschen (Request $request) {

        $Kommentar = Vknummern_Kommentar::find($request->input('id'));
        $Kommentar->delete();

        return redirect() -> back() -> with(['Meldung' => 'Der Kommentar wurde gelöscht.', 'type' => 'success']);
    }

    public function Vergabe($NummernID){

        $Interessenten=$this->NummernRepository->InteressentenOhneNummer();

        return view('VKnummern.Vergabe', [
            'Interessenten' => $Interessenten,
            'Nummer' => $this->NummernRepository->getVKNummer($NummernID)
        ]);    
    }

}