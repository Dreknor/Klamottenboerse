<?php

/**
 * Created by PhpStorm.
 * User: DDR
 * Date: 01.10.2016
 * Time: 10:53
 */

namespace App\Repositories\Warenkorb;

use App\Model\warenkorb;
use Illuminate\Support\Facades\Auth;

class WarenkorbRepository
{

    public function getWarenkorb(){
        return warenkorb::query()
            ->where("user_id", Auth::user()->id)
            ->get();
    }

    public function getWarenkorbPaginate(){
        return warenkorb::query()
            ->where("user_id", Auth::user()->id)
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
    }

    public function sumWarenkorb(): string
    {
        return number_format(warenkorb::query()
            ->where("user_id", Auth::user()->id)
            ->sum('betrag'), 2);
    }

    public function getArticle($ArticleID){
        return warenkorb::query()
            ->where('id', $ArticleID)
            ->first();
    }

    public function leereWarenkorb(){
        return warenkorb::query()
            ->where("user_id", Auth::user()->id)
            ->delete();
    }

}
