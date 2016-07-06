<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 05.07.2016
 * Time: 05:57
 */

namespace App\Http\Controllers;


use App\Repositories\Verkaeufernummern\NummernRepository;

class NummernController extends Controller
{
    public function __construct(NummernRepository $nummernRepository)
    {
        $this->middleware('auth');
        $this->NummernRepository = $nummernRepository;
    }

    public function index(){

        return view('vknummern.uebersicht', [
            'Nummern' => $this->NummernRepository->all()
        ]);
    }
}