<?php

namespace App\Repositories\Interessenten;

use App\Model\Interessenten;

class InteressentenRepository
{
    public function all()
    {
        return Interessenten::with('vknummern_vergeben')->get();
    }

    public function find($id)
    {
        return Interessenten::find($id);
    }
}
