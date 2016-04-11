<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.03.2016
 * Time: 21:59
 */

namespace App\Http\Controllers;
use App\Http\Requests\InteressentenAnlegenRequest;
use App\Models\Dateien\Dateien;
use App\Models\Interessenten\Interessenten;
use App\Repositories\Interessenten\InteressentenRepository;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Mail;

class InteressentenController extends Controller
{
    public function __construct(InteressentenRepository $interessentenRepository)
    {
        $this->middleware('auth');
        $this->interessentenRepository = $interessentenRepository;
    }

    /**
     * Zeigt eine Übersicht über alle erfassten Interessenten
     *
     * @param string $Gruppe
     * @return \Illuminate\Http\Response
     */
    public function index($Gruppe="")
    {

        switch ($Gruppe) {
            case "Kinderhaus":
                return view('interessenten', [
                    "entries" => $this->interessentenRepository->Kinderhaus(),
                    "Gruppe"  => $Gruppe
                ]);
                exit;

            case "Mitarbeiter":
                return view('interessenten', [
                    "entries" => $this->interessentenRepository->Mitarbeiter(),
                    "Gruppe"  => $Gruppe
                ]);
                exit;

            default:
                return view('interessenten', [
                    "entries" => $this->interessentenRepository->all(),
                    "Gruppe"  => "All"
                ]);
                exit;
        }
    }



    public function search(Request $request) {

        return view('interessenten',[
            "entries" => $this->interessentenRepository->search($request->input('SearchString'))]);
    }

    public function show ($id) {
        $Interessent=$this->interessentenRepository->findInteressent($id);
        $Dateien=Dateien::query()->get();

        return view('Interessent', [
           'Interessent' => $Interessent,
            'Dateien'   => $Dateien
        ]);
    }

    public function warningDelete ($id) {
        $Interessent=$this->interessentenRepository->findInteressent($id);
        return view('deleteInteressent', [
            'Interessent' => $Interessent
        ]);
    }

    public function destroy ($id) {
        $Interessent=$this->interessentenRepository->findInteressent($id);
       $Interessent->delete();
        return redirect(action('InteressentenController@index'));
    }

    public function update(Request $request) {

            $Daten[$request->input('name')]= $request->input('value');

            $Interessent=Interessenten::query()->findOrFail( $request->input('pk'));
            $Interessent->fill($Daten);

            if($Interessent->save())
                return response()->json(['status' => '1']);
            else
                return response()->json(['status' => '1']);


    }

    public function store (InteressentenAnlegenRequest $request) {

        $lastInsertedId = Interessenten::create($request->all())->id;
        return redirect('/Interessent/'.$lastInsertedId);

    }

    public function export ($string) {

        switch ($string){
            case "All":
                Excel::create('Interessenten', function($excel) {

                    $excel->sheet('Interessenten', function($sheet) {
                        $sheet->fromModel(Interessenten::all());
                    });

                })->export('xls');
                exit;

            case "Kinderhaus":
                Excel::create('Kinderhauseltern', function($excel) {

                    $excel->sheet('Kinderhauseltern', function($sheet) {
                        $sheet->fromModel($this->interessentenRepository->Kinderhaus());
                    });

                })->export('xls');

            case "Mitarbeiter":
                Excel::create('Mitarbeiter', function($excel) {

                    $excel->sheet('Mitarbeiter', function($sheet) {
                        $sheet->fromModel($this->interessentenRepository->Mitarbeiter());
                    });

                })->export('xls');
                exit;
        }

        return redirect(action('InteressentenController@index'));
    }


    public function abmelden ($id, $token) {
        $Interessent=$this->interessentenRepository->findInteressent($id);
        if (is_object($Interessent) and $token==$Interessent->mail){

            return view('externeAufrufe.abmelden', [
                'gefunden' => 'ja',
                'id' => $id,
                'token' => $token,
            ]);

        } else {
            return view('externeAufrufe.abmelden', ['gefunden' => 'nein',]);


        }
    }

    public function doAbmelden($id, $token){
        $Interessent=$this->interessentenRepository->findInteressent($id);
        if ($Interessent->mail==$token){
           
            /*
             * Benachrichtigen über Abmeldung
             */
           
            Mail::send(array('text' => 'emails.benachrichtigungAbmeldung'),[
                'vorname'   => $Interessent->vorname,
                'nachname'   => $Interessent->nachname,
                'mail'   => $Interessent->mail,
                'telefon'   => $Interessent->telefon,
            ], function($message) {
                // note: if you don't set this, it will use the defaults from config/mail.php

                $message->from('anmeldung@klamottenboerse.de', 'Klamottenbörse');
                $message->to('anmeldung@klamottenboerse.de', 'Klamottenbörse')
                    ->subject('Abmeldung Klamottenbörse');
            });
            $Interessent->delete();
            
        }
        return view('externeAufrufe.abmeldenFertig');

    }
}