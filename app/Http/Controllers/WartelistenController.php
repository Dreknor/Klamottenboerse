<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 07:40
 */

namespace App\Http\Controllers;

use App\Model\Warteliste;

class WartelistenController extends Controller
{
    public function set($interessentenID)
    {
        $Eintrag = Warteliste::firstOrCreate(['interessenten_id' =>$interessentenID]);

        return redirect()->back()->with(['success' => 'Interessent auf Warteliste gesetzt.']);
    }

    public function drop($interessentenID)
    {
        $Warteliste = Warteliste::where('interessenten_id', $interessentenID)->first();
        $Warteliste->delete();

        return redirect()->back()->with(['success' => 'von Warteliste gelöscht.']);
    }
}
