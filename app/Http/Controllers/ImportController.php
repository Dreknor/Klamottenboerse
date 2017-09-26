<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 20:53
 */

namespace App\Http\Controllers;


use App\Models\Interessenten\Interessenten;
use App\Models\Klamottenboerse\Vknummern;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{


    public function __construct(KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->middleware('auth');
        $this->klamottenboersenRepository = $klamottenboersenRepository;
    }

    public function Import() {
        $Daten=Excel::load('storage\app\import\Daten.xlsx', function($reader) {
            // Getting all results
            $results = $reader->get();

            foreach ($results AS $Result){
              if (is_null($Result->vorname)){
                    $Result->vorname="unbekannt";
                }
                $Interessent=Interessenten::query()
                    ->firstOrCreate([
                        "vorname" => trim($Result->vorname),
                        "nachname" => trim($Result->nachname)
                    ]);
                $Interessent->mail=trim($Result->mail);
                $Interessent->telefon=trim($Result->telefon);
                $Interessent->handy=trim($Result->handy);
                $Interessent->save();

                if ($Result->frueh2015 >0){
                    $vknummer2015=Vknummern::query()
                        ->firstOrCreate([
                            'vknummer' => $Result->frueh2015,
                            'klamottenboersen_id' => 2,
                            'vergeben_an' => $Interessent->id]);
                }

                if (is_float($Result->herbst2014)){
                    $vknummer2014=Vknummern::query()
                        ->firstOrCreate([
                            'vknummer' => $Result->herbst2014,
                            'klamottenboersen_id' => 1,
                            'vergeben_an' => $Interessent->id]);
                }
            }

            $this->Import_csv();
        });

        return view('welcome');
    }

    public function Import_csv() {
        $Daten=Excel::load('storage\app\import\Export.xlsx', function($reader) {
            // Getting all results
            $results = $reader->get();

            foreach ($results AS $Result){
                if (is_null($Result->vorname)){
                    $Result->vorname="unbekannt";
                }
                $Interessent=Interessenten::query()
                    ->firstOrCreate([
                        "vorname" => trim($Result->vorname),
                        "nachname" => trim($Result->nachname)
                    ]);
                $Interessent->mail=trim($Result->mail);
                $Interessent->telefon=trim($Result->telefon);
                $Interessent->handy=trim($Result->handy);
                $Interessent->save();

            }
        });

        
    }

    public function index()
    {
        return view('klamottenboerse.importExport');
    }

    public function importExcel(Request $request)
    {

        $Klamottenboerse = $this->klamottenboersenRepository->latest();


        if($request->hasFile('import_file')){

            $path = $request->import_file->path();
            $data = Excel::selectSheets('Nummern')->load($path, function($reader) {
            })->get();




            if(!empty($data) && $data->count()){

                foreach ($data as $key => $value) {
                    if ($value->vknummer > 0) {
                        $VKNummer=Vknummern::query()
                            ->where('vknummer', '=', $value->vknummer)
                            ->where('klamottenboersen_id', '=', $Klamottenboerse->id)
                            ->first();
                        $VKNummer->umsatz = $value->sum;
                        $VKNummer->save();
                    }


                }







            }
        }

        return redirect(url('Ueberblick'));
    }

}