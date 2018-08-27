<?php

namespace App\Http\Controllers;

use App\Models\Klamottenboerse\Vknummern;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;


class ExportController extends Controller
{
    //

    public function __construct(KlamottenboersenRepository $klamottenboersenRepository, NummernRepository $nummernRepository)
    {
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->nummernRepository = $nummernRepository;
        $this->middleware('auth');
    }

    public function export(){

        $Verkaeufer=  $this->nummernRepository->getVerkaeufer2();
        $Ausgabe = "";

        foreach ($Verkaeufer AS $Person){
            $Ausgabe.= "/r$Person->vknummer;$Person->vorname;$Person->nachname";
        }
       return $Ausgabe;
    }

    public function downloadExcel()
    {


        $data = Vknummern::query()
            ->where('vergeben_an', '>', 0)
            ->where('klamottenboersen_id', '=', $this->klamottenboersenRepository->getId() )
            ->join('interessenten', 'vknummern.vergeben_an', '=', 'interessenten.id')
            ->select([ 'vknummer','vorname', 'nachname'])
            ->get()
            ->toArray();

        return Excel::create('VKnummern', function($excel) use ($data) {
            $excel->sheet('VKnummern', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });
        })->download('xls');
    }
}
