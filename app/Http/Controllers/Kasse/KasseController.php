<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 01.10.2016
 * Time: 09:11
 */

namespace App\Http\Controllers\Kasse;


use App\Http\Controllers\Controller;
use App\Model\verkaeufe;

use App\Model\verkaufteartikel;
use App\Model\vknummern;
use App\Model\warenkorb;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Nummern\VKnummerRepository;
use App\Repositories\Warenkorb\WarenkorbRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KasseController extends Controller
{

    protected $WarenkorbRepository;
    protected $klamottenboersenRepository;
    protected $VKnummerRepository;

    public function __construct(WarenkorbRepository $warenkorbRepository, KlamottenboersenRepository $klamottenboersenRepository, VKnummerRepository $VKnummerRepository)
    {
        $this->middleware('auth');
        $this->WarenkorbRepository = $warenkorbRepository;
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->VKnummerRepository = $VKnummerRepository;
    }

    public function index()
    {

        return view('kasse.home')->with([
            "warenkorb" => $this->WarenkorbRepository->getWarenkorbPaginate(),
            "summe" => $this->WarenkorbRepository->sumWarenkorb()
        ]);
    }

    public function ArtikelInWarenkorb(Request $request)
    {

        $vknummern = \Cache::remember('vknummern', 5, function () {
            $nummern = $this->VKnummerRepository->allLatest();
            return $nummern->filter(function ($item) {
                return $item->vergeben_an != null;
            })->pluck('vknummer');
        });


        $request->validate([
            'vknummer' => [
                'required',
                function ($attribute, $value, $fail) use ($vknummern) {
                    if (!$vknummern->contains($value)) {
                        $fail('Verkäufernummer nicht vergeben');
                    }
                }
            ],
            'artikelnummer' => 'required',
            'betrag' => 'required'
        ]);



            $Artikel = new warenkorb();
            $Artikel->user_id = Auth::user()->id;
            $Artikel->fill($request->all());

            $Artikel->save();

            return redirect(url("/kasse"));


    }

    public function editArticle($ArticleID){

        $Artikel=$this->WarenkorbRepository->getArticle($ArticleID);

        if (!$Artikel){
            return redirect(url("/kasse"))->with([
                'Meldung' => 'Artikel nicht gefunden',
                'type' => 'danger'
            ]);
        }
        $Artikel->delete();

        return view('kasse.home')->with([
            "artikel" => $Artikel,
            "warenkorb" => $this->WarenkorbRepository->getWarenkorbPaginate(),
            "summe" => $this->WarenkorbRepository->sumWarenkorb()
        ]);
    }

    public function bezahlen(){

        $Summe = $this->WarenkorbRepository->sumWarenkorb();
        $Warenkorb = $this->WarenkorbRepository->getWarenkorb();

        if ($Warenkorb->count() > 0){
            $verkauf=new verkaeufe();
            $verkauf->summe = $Summe;
            $verkauf->user_id = Auth::user()->id;
            $verkauf->klamottenboerse_id = $this->klamottenboersenRepository->aktuelleKlamottenboerse()->id;
            $verkauf->save();

            foreach ($Warenkorb as $Article){
                $Position["verkauf"] = $verkauf->id;
                $Position['klamottenboerse_id'] = $verkauf->klamottenboerse_id;
                $Position["vknummer"] = $Article->vknummer;
                $Position["artikelnummer"] = $Article->artikelnummer;
                $Position["betrag"] = $Article->betrag;
                $Artikel[] = $Position;

            }

            verkaufteartikel::insert($Artikel);

           $Warenkorb = $this->WarenkorbRepository->leereWarenkorb();

            return view('kasse.kasse.bezahlen', [
                "Summe" => $Summe
            ]);
        }

        return redirect(url("/kasse"))->with([
            'Meldung' => 'Keine Artikel im Warenkorb',
            'type' => 'danger'
        ]);



    }

    public function wechselgeld (Request $request){

        $Wechselgeld = $request->input('gegeben') - $request->input('betrag');

        return view('kasse.kasse.wechselgeld', [
            "Wechselgeld" => $Wechselgeld
        ]);
    }
}
