<?php

namespace App\Repositories\Verkaeufe;


use App\Model\verkaeufe;

class VerkaeufeRepository
{

    public function getAlleVerkaeufe(){
        return verkaeufe::query()
            ->orderBy('created_at', 'DESC')
            ->get();
    }

    public function getVerkauf($VerkaufsID){
        return verkaeufe::query()
            ->where('id', $VerkaufsID)
            ->first();
    }


}
