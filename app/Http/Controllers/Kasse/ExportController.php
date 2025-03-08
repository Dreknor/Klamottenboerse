<?php

namespace App\Http\Controllers\Kasse;

use App\Exports\AbrechnungExport;
use App\Http\Controllers\Controller;
use App\Model\verkaeufe;
use App\Model\verkaufteartikel;
use App\Model\VKnummer;
use Maatwebsite\Excel\Facades\Excel;
use App\Model\vknummern;

class ExportController extends Controller
{


    public function downloadExcel()
    {

        $Nummern = VKnummer::query()
        ->orderBy('vknummer', 'ASC')
        ->aktuelleKlamottenboerse()
        ->get();

        $data[1]=verkaufteartikel::query()
            ->groupBy('vknummer')
            ->selectRaw('vknummer, sum(betrag) as sum')
            ->orderBy('sum', 'DESC')
            ->get();
        $data[2]=verkaufteartikel::all();
        $data[3]=verkaeufe::all();


        return Excel::download(new AbrechnungExport(), 'VKNummern.xlsx');
    }



}
