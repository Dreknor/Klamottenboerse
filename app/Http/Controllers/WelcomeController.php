<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Models\Interessenten\Nachrichten;
use App\Models\Klamottenboerse\Klamottenboerse;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use App\Repositories\Verkaeufernummern\NummernRepository;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository, InteressentenRepository $interessentenRepository, NummernRepository $nummernRepository)
    {
        $this->middleware('auth');
        $this->klamottenboersenRepository = $klamottenboersenRepository;
        $this->interessentenRepository = $interessentenRepository;
        $this->nummernRepository = $nummernRepository;


    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('welcome', [
            "Klamottenboerse"   => $this->klamottenboersenRepository->latest(),
            "Nachrichten"       => Nachrichten::query()->orderBy('created_at', 'DESC')->take(10)->with('Interessent')->get(),
            'Interessenten'     => $this->interessentenRepository->countAll(),
            'Verkaeufer'        => $this->nummernRepository->countVerkaeufer(),
            "Klamottenboersen"  => Klamottenboerse::with('vknummern')->orderBy('datum', 'DESC')->get()
        ]);
    }
}
