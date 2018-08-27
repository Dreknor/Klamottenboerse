<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 27.03.2016
 * Time: 07:31
 */

namespace App\Http\Controllers;


use App\Models\Klamottenboerse\Helfer;
use App\Models\Klamottenboerse\Klamottenboerse;
use App\Models\Klamottenboerse\Vknummern;
use App\Models\Klamottenboerse\Warteliste;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class KlamottenboersenController extends Controller
{
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository, NummernRepository $nummernRepository)
    {
        $this->middleware('auth');
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->nummernRepository = $nummernRepository;
    }

    public function index(){

        return view('klamottenboerse.grunddaten', [
            'Klamottenboerse' => $this->klamottenboersenRepository->latest()
        ]);
    }

    public function update(Request $request) {
        $Daten[$request->input('name')]= $request->input('value');

        $Klamottenboerse=Klamottenboerse::query()->findOrFail( $request->input('pk'));
        $Klamottenboerse->fill($Daten);

        if($Klamottenboerse->save())
            return response()->json(['status' => '1']);
        else
            return response()->json(['status' => '1']);


    }

    public function neueKlamottenboerse() {
        return view('klamottenboerse.neueKlamottenboerse');
    }
    
    public function store(Request $request){


        $alleNummern=$this->nummernRepository->all();
        $id=Klamottenboerse::create($request->all())->id;

        if ($alleNummern != ""){
            $data=array();
            foreach ($alleNummern as $Nummer) {
                $data[] = array('vknummer' => $Nummer->vknummer, 'klamottenboersen_id'=>$id, 'reserviert_fuer' => $Nummer->reserviert_fuer );
            }

            Vknummern::insert($data);
            DB::table('warteliste')->truncate();


            return redirect('Grunddaten');

        } else {

            return redirect('Nummern/new');
        }



    }

    public function destroy ($id) {
        $Helfer=Helfer::query()->findOrFail($id);
        $Helfer->delete();
        return redirect(action('KlamottenboersenController@index'));
    }
    
    public function store_Helfer (Request $request){

        $Klamottenboerse = $this->klamottenboersenRepository->latest();

        Helfer::updateOrCreate(
            ["klamottenboerse_id" => $Klamottenboerse->id, "name" => $request->input('name')],
            ["telefon" => $request->input('telefon'),
            "mail" => $request->input('mail'),
            "bereich" => $request->input('bereich')
        ]);


        //return view('klamottenboerse.grunddaten');
        return back();
    }

}
