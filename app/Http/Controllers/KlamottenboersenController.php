<?php
/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 27.03.2016
 * Time: 07:31
 */

namespace App\Http\Controllers;


class KlamottenboersenController extends Controller
{
    public function index(){
        return view('klamottenboerse.grunddaten');
    }
}