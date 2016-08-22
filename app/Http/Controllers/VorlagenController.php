<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 22.08.2016
 * Time: 21:19
 */

namespace App\Http\Controllers;


use App\Repositories\Mailvorlagen\VorlagenRepository;

class VorlagenController extends Controller
{
    public function __construct(VorlagenRepository $vorlagenRepository){
        $this->VorlagenRepository=$vorlagenRepository;
    }

    public function index(){
        return view('mailvorlagen.mailvorlagen',[
          "Vorlagen" => $this->VorlagenRepository->alle()
        ]);
    }
}