<?php

namespace App\Http\Controllers\Kasse;

use App\Http\Controllers\Controller;
use App\Model\settings;
use App\Model\verkaeufe;
use App\Model\verkaufteartikel;

use App\Http\Requests;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Support\Facades\App;
use Knp\Snappy\Pdf;

class AbrechnungsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

    }

    public function perform(){




        $Settings = settings::query()->first();
        $VKnummern = VKnummer::query()->AktuelleKlamottenboerse()->get();
        $verkaufteArtikel = verkaufteartikel::query()->get();

        foreach ($VKnummern as $VKnummer) {
            $VKnummer->update([
                "umsatz" => $verkaufteArtikel->where('vknummer', $VKnummer->vknummer)->sum('betrag'),
                ]);
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




        $pdf = App::make('dompdf.wrapper');
        $pdf = $pdf->loadView('kasse.pdf.abrechnung3', [
            "Vknummern" => VKnummer::query()->aktuelleKlamottenboerse()->get()->sortBy('vknummer'),
            "VKNummernNachUmsatz" => verkaufteartikel::query()
                ->groupBy('vknummer')
                ->selectRaw('vknummer, sum(betrag) as sum')
                ->orderBy('sum', 'DESC')
                ->get(),
            "Settings" => $Settings,
            "Umsatz" => $Umsatz,
            "Erloes" => $BetragKinderhaus,
            "Teile"     => verkaufteartikel::query()->count(),
            "Kunden" => verkaeufe::query()->count(),
            "erfolgreichsteVKnummer" => $vknummer,
            'verkaufteArtikel' => $verkaufteArtikel,
        ]);

        return $pdf->download(date('Y_m_d_H_i').'_Uhr_Abrechnung.pdf');



    }

   /* public function perform(){
        set_time_limit ( 600 );
        $Settings = settings::query()->first();
        $VKnummern = vknummern::query()->get();


        $Umsatz = verkaeufe::query()->sum('summe');
        $BetragKinderhaus = $Umsatz/100*$Settings->provision;


        $pdf = app('dompdf.wrapper');
        $pdf = $pdf->loadView('pdf.abrechnung', [
            "Vknummern" => vknummern::all(),
            "Settings" => $Settings,
            "Umsatz" => $Umsatz,
            "Erloes" => $BetragKinderhaus,
            "Teile"     => verkaufteartikel::query()->count(),
            "Kunden" => verkaeufe::query()->count(),
            "erfolgreichsteVKnummer" => verkaufteartikel::query()
                ->groupBy('vknummer')
                ->selectRaw('vknummer, sum(betrag) as sum')
                ->orderBy('sum', 'DESC')
                ->first()
        ]);

        return $pdf->download(date('Y_m_d_H_i').'_Uhr_Abrechnung.pdf');

        return view("pdf.abrechnung");
    }*/
}
