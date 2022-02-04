<?php

namespace App\Http\Controllers;

use App\Model\Notizen;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class NotizenController extends Controller
{
    public function store($InteressentenID, Request $request){
        $notiz = Notizen::firstOrNew([
            "interessenten_id" => $InteressentenID
        ]);

        $notiz->notiz = $request->input('notiz');
        $notiz->save();

        return redirect()->back();
    }
}
