<?php

namespace App\Http\Controllers;

use App\Models\Klamottenboerse\Klamottenboerse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Klamottenboerse= Klamottenboerse::query()
                            ->where('id', ">=", 1 )
                            ->first();
        if (!isset($Klamottenboerse->id)){
                return view('klamottenboerse.setupKlamottenboerse');
        } else {

                return view('home');
        }

    }
}
