<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 31.08.2016
 * Time: 07:40
 */

namespace App\Http\Controllers;



use App\Models\Klamottenboerse\Warteliste;
use Illuminate\Http\Request;


class WartelistenController extends Controller
{
    public function set($InteressentenID){
        $Eintrag = Warteliste::firstOrCreate(['interessenten_id' =>$InteressentenID]);
        return redirect()->back()->with(['Message' => 'Interessent auf Warteliste gesetzt.', 'Type' => 'success']);

    }

    public function drop(Request $request){
        $Warteliste= Warteliste::find($request->id);
        $Warteliste->delete();
        return redirect()->back()->with(['Message' => 'von Warteliste gelöscht.', 'Type' => 'success']);
    }
}