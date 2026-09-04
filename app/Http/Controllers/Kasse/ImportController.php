<?php

namespace App\Http\Controllers\Kasse;

use App\Model\settings;
use App\Model\verkaeufe;
use App\Model\verkaufteartikel;
use App\Model\vknummern;
use App\Model\warenkorb;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('settings.importExport');
    }

    public function importExcel(Request $request)
    {


        if($request->hasFile('import_file')){

            $path = $request->import_file->path();
            $data = Excel::load($path, function($reader) {
            })->get();




            if(!empty($data) && $data->count()){

                foreach ($data as $key => $value) {
                    if ($value->vknummer > 0) {
                        $insert[] = ['vknummer' => $value->vknummer, 'vorname' => $value->vorname, 'nachname' => $value->nachname];
                    }


                }

                if(!empty($insert)){


                    vknummern::insert($insert);

                    return redirect(url('settings'));
                }


            }
        }

        return back();

    }


}
