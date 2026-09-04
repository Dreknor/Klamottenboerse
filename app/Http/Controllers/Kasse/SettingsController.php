<?php

namespace App\Http\Controllers\Kasse;

use App\Http\Controllers\Controller;
use App\Model\settings;
use App\Model\verkaeufe;
use App\Model\verkaufteartikel;
use App\Model\VKnummer;
use App\Model\warenkorb;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;

use App\Http\Requests;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(){

        $klamottenboerse = (new KlamottenboersenRepository())->aktuelleKlamottenboerse();
        $Settings = settings::query()
            ->where('datum', $klamottenboerse->datum)
            ->orderBy('datum', 'DESC')
            ->first();

        if (!$Settings){
            $Settings = new settings([
                "name" => "Klamottenbörse",
                "kinderhaus" => "ev. Kinderhaus der Friedenskirchgemeinde Radebeul",
                "datum" => $klamottenboerse->datum,
                "provision" => 25,
            ]);
            $Settings->save();
        } elseif ($Settings->datum != (new KlamottenboersenRepository())->aktuelleKlamottenboerse()->datum){
            $Settings = new settings([
                "name" => $Settings->name,
                "kinderhaus" => $Settings->kinderhaus,
                "datum" => (new KlamottenboersenRepository())->aktuelleKlamottenboerse()->datum,
                "provision" => $Settings->provision,
            ]);
            $Settings->save();
        }

        $Umsatz = verkaeufe::query()->sum('summe');
        $BetragKinderhaus = $Umsatz/100*$Settings->provision;

        $erfolgreichsteVKnummer = verkaufteartikel::query()
            ->groupBy('vknummer')
            ->selectRaw('vknummer, sum(betrag) as sum')
            ->orderBy('sum', 'DESC')
            ->first();

        if ($erfolgreichsteVKnummer){
            $vknummer = VKnummer::query()->where('vknummer', $erfolgreichsteVKnummer->vknummer)->where('klamottenboersen_id', (new KlamottenboersenRepository())->aktuelleKlamottenboerse()->id)->first();
        } else {
            $vknummer = null;
        }


        return view('kasse.settings.index', [
            "Settings" => $Settings,
            "Teile"     => verkaufteartikel::query()->count(),
            "Kunden" => verkaeufe::query()->count(),
            "Umsatz" => $Umsatz,
            "Kinderhaus" => $BetragKinderhaus,
            "erfolgreichsteVKnummer" => $vknummer,
            "warenkorb" => warenkorb::query()->count()
        ]);
    }

    public function save(Request $request){

        $Klamottenboerse=settings::query()
            ->orderBy('datum', 'DESC')
            ->first();
        $Klamottenboerse->fill($request->all());
        $Klamottenboerse->save();

        return redirect()->back()->with([
            'Meldung' => 'Einstellungen gespeichert',
            'type' => 'success'
        ]);

    }
}
