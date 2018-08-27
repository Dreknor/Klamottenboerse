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
use App\Models\Klamottenboerse\Warteliste;
use App\Repositories\Dateien\DateienRepository;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Nachrichten\NachrichtenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class NummernController extends Controller
{
    public function __construct(NummernRepository $nummernRepository, KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->middleware('auth');
        $this->NummernRepository = $nummernRepository;
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->Klamottenboerse=$klamottenboersenRepository->getId();
    }

    public function index(){


        $Daten=Vknummern::with('vergeben_an_Interessent', 'reserviert_fuer_Interessent', 'Kommentar')
            ->where('klamottenboersen_id', '=', $this->klamottenboersenRepository->getId())
            ->orderBy('vknummer')
            ->get();

        if (count($Daten)>0){
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

                    //$Nummer->reserviert = $Interessenten->findInteressent($Nummer->reserviert_fuer);


                }

                if (is_integer($Nummer->vergeben_an) ){
                    $Count['vergeben']++;

                    //$Nummer->vergeben = $Interessenten->findInteressent($Nummer->vergeben_an);
                }

                $Nummern[]=$Nummer;

            }



            return view('vknummern.uebersicht', [
                'Nummern' => $Daten,
                'Count' => $Count,
            ]);
        } else {

            return redirect('Nummern/new')->with([
                "Meldung" => "Es müssen Verkäufernummern angelegt werden",
                "type"      => "warning"
            ]);

        }

    }
    
    public function newNummer () {
        return view('vknummern.neueNummer', [
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

        return view('vknummern.reserviereNummer', [
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
        //dd($request);

        $Nummer=$this->NummernRepository->storeNummer($request->input('InteressentenID'), $request->input('NummernID'));
        $Interessent= InteressentenRepository::findInteressent($request->input('InteressentenID'));
       if ($Nummer==1){
           
           $Warteliste= Warteliste::where('interessenten_id', $Interessent->id)->first();

           if (isset($Warteliste->interessenten_id)){
               $Warteliste->delete();
           }

           $Interessent= InteressentenRepository::findInteressent($request->input('InteressentenID'));
           $VKnummer=$this->NummernRepository->getVKNummer($request->input('NummernID'));

           //Uhrzeiten formatieren
           $Klamottenboerse=$this->klamottenboersenRepository->latest();
           $Klamottenboerse->abholung_von = date('G.i', strtotime($Klamottenboerse->abholung_von));

           //Erstelle VerkäuferInfos
           $pdf = App::make('dompdf.wrapper');
           $pdf->loadView('listen.pdf.verkaeuferinfos',[
               "Klamottenboerse" => $Klamottenboerse,
               "VKnummer"   => $VKnummer
           ]);
           $pdf->save(storage_path().'/app/anhaenge/VerkaeuferInfos.pdf');



           $text = View::make('emails.vergabeVKNummer', [
               'Interessent'=> $Interessent,
               'VKNummer' => $VKnummer,
               'Klamottenboerse' => $this->klamottenboersenRepository->latest(),
               'Absender'   => Auth::user()->name
           ]);
           
           $Nachricht=[
               'betreff' => 'Nummernvergabe Klamottenbörse',
               'nachricht'   => $text,
               'anhang' => 'VerkaeuferInfos.pdf',
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
        $Nummern=$this->NummernRepository->nichtreservierteUndNichtVergebeneNummern();

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

        return view('vknummern.Vergabe', [
            'Interessenten' => $Interessenten,
            'Nummer' => $this->NummernRepository->getVKNummer($NummernID)
        ]);    
    }
    
    public function VerkaeuferAnzeigen ($Vknummer){
        
        
        return view('vknummern.VerkaeuferAnzeigen',[
            'VerkaeuferArray' => $this->NummernRepository->VerkaeuferNummer($Vknummer),
            'Nummer' => $Vknummer
        ]);
    }

}