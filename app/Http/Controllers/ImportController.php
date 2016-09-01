<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 20:53
 */

namespace App\Http\Controllers;


use App\Models\Interessenten\Interessenten;
use Maatwebsite\Excel\Facades\Excel;

class ImportController extends Controller
{

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
                        "vorname" => $Result->vorname,
                        "nachname" => $Result->nachname
                    ]);
                $Interessent->mail=$Result->mail;
                $Interessent->telefon=$Result->telefon;
                $Interessent->handy=$Result->handy;
                $Interessent->save();
            }

            return view('welcome');
        });

        ;
    }


}