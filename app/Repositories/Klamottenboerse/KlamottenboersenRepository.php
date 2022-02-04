<?php

namespace App\Repositories\Klamottenboerse;


use App\Model\Klamottenboerse;

class KlamottenboersenRepository
{

    public function all(){
        return Klamottenboerse::all();
    }

    public function find($id){
        return Klamottenboerse::find($id);
    }

    public function aktuelleKlamottenboerse(){
        return Klamottenboerse::query()->latest()->first();
    }

}