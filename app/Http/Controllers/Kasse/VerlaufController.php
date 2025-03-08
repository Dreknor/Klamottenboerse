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
use App\Model\VKnummer;
use App\Model\vknummern;
use App\Model\warenkorb;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Nummern\VKnummerRepository;
use App\Repositories\Verkaeufe\VerkaeufeRepository;
use App\Repositories\Warenkorb\WarenkorbRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function React\Promise\all;

class VerlaufController extends Controller
{
    protected $VerkaeufeRepository;
    protected $klamottenboersenRepository;
    protected $VKnummerRepository;

    public function __construct(VerkaeufeRepository $verkaeufeRepository, KlamottenboersenRepository $klamottenboersenRepository, VKnummerRepository $VKnummerRepository)
    {
        $this->middleware('auth');
        $this->VerkaeufeRepository = $verkaeufeRepository;
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->VKnummerRepository = $VKnummerRepository;

    }

    public function index()
    {

        $Verkaeufe= verkaeufe::query()
            ->orderBy('created_at', 'DESC')
            ->with('artikel')
            ->paginate(15);

        return view('kasse.verkaeufe.verkaeufe',[
            "Verlauf" => $Verkaeufe
        ]);
    }

    public function verkaeufer(){

        $vknummern = VKnummer::query()
            ->whereNotNull('vergeben_an')
            ->orderBy('vknummer', 'ASC')
            ->where('klamottenboersen_id', $this->klamottenboersenRepository->aktuelleKlamottenboerse()->id)
            ->with('verkaufteArtikel')
            ->with('vergeben_an_Interessent')
            ->paginate(15);




        return view('kasse.verkaeufe.verkaeufer',[
            "Verlauf" => $vknummern
        ]);
    }

    public function activEdit(){
        $Warenkorb=warenkorb::query()
            ->where('user_id', Auth::user()->id)
            ->count();


        if ($Warenkorb > 0){
            return redirect()->back()->with([
                'Meldung' => 'Es befinden sich noch Artikel im Warenkorb',
                'type' => 'danger'
            ]);

        }

        $Verkaeufe= verkaeufe::query()
            ->orderBy('created_at', 'DESC')
            ->with('artikel')
            ->paginate(15);

        $Fehler = "";
        $Edit = true;


        return view('kasse.verkaeufe.verkaeufe',[
                "Verlauf" => $Verkaeufe,
                "Fehler" => $Fehler,
                "edit" => $Edit
        ]);

    }


    public function editVerkauf($VerkausID){

        $Verkauf = $this->VerkäufeRepository->getVerkauf($VerkausID);

        if ($Verkauf and ($Verkauf->user_id == Auth::user()->id or Auth::user()->verwaltung == 1)){
            $Artikel = [];
            $VerkaufteArtikel=$Verkauf->artikel;

            foreach ($VerkaufteArtikel as $Article){
                $Artikel[]= [
                    "user_id" => Auth::user()->id,
                    "vknummer" => $Article->vknummer,
                    "artikelnummer" => $Article->artikelnummer,
                    "betrag" => $Article->betrag
                ];

            }
            warenkorb::insert($Artikel);

            $Verkauf->artikel()->delete();
            $Verkauf->delete();

           return redirect(url("/kasse"));

        } else {
            return redirect(url("kasse/verlauf"))->with([
                'Meldung' => 'Verkauf nicht gefunden',
                'type' => 'danger'
            ]);
        }
    }
}
