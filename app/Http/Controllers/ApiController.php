<?php

namespace App\Http\Controllers;

use App\Model\Klamottenboerse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function termin()
    {
        $Klamottenboerse = Klamottenboerse::query()->orderByDesc('created_at')->first();

        return $Klamottenboerse->datum->format('d.m.Y');
    }

    public function anmeldung()
    {
        $Klamottenboerse = Klamottenboerse::query()->orderByDesc('created_at')->first();

        if (Carbon::now()->lessThan($Klamottenboerse->anmeldung)) {
            return 'Die Anmeldung für die nächste Klamottenbörse ist am <b>'.$Klamottenboerse->anmeldung->format('d.m.Y').'</b> möglich.</p>';
        } elseif (Carbon::now()->lessThan($Klamottenboerse->datum)) {
            return 'Die Anmeldung für die akutelle Klamottenbörse ist leider nicht mehr möglich.';
        } else {
            return 'Der Termin für Anmeldung zur nächsten Klamottenbörse wird demnächst hier bekannt gegeben.';
        }
    }
}
