<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Model\Notizen;
use Illuminate\Http\Request;

class NotizenController extends Controller
{
    public function store($InteressentenID, Request $request)
    {
        $notiz = Notizen::firstOrNew([
            'interessenten_id' => $InteressentenID,
        ]);

        $notiz->notiz = $request->input('notiz');
        $notiz->save();

        return redirect()->back();
    }
}
