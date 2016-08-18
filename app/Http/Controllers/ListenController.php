<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 16.08.2016
 * Time: 09:45
 */

namespace App\Http\Controllers;


use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Facades\App;

class ListenController extends Controller
{
    public function __construct(InteressentenRepository $interessentenRepository, NummernRepository $nummernRepository, KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->middleware('auth');
        $this->interessentenRepository = $interessentenRepository;
        $this->nummernRepository = $nummernRepository;
        $this->klamottenboersenRepository = $klamottenboersenRepository;
    }

    public function index(){
        return view('listen.listenuebersicht');
    }

    public function vknummern (){


        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('listen.pdf.vknummern',[
            "Klamottenboerse" => $this->klamottenboersenRepository->latest(),
            "Nummern"   => $this->nummernRepository->getNummernMitInteressenten(),
            "alteNummer" => 100
        ]);
        return $pdf->download('vknummern.pdf');


    }
    
    public function belehrung(){
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('listen.pdf.vknummern',[
            "Klamottenboerse" => $this->klamottenboersenRepository->latest(),
            "Nummern"   => $this->nummernRepository->getNummernMitInteressenten(),
            "alteNummer" => 100
        ]);
        return $pdf->download('vknummern.pdf');
    }
}