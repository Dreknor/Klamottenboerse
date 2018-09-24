<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateKlamottenboerseRequest;
use App\Model\Klamottenboerse;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;

class KlamottenboersenController extends Controller
{
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->klamottenboersenRepository = $klamottenboersenRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\klamottenboerse  $klamottenboerse
     * @return \Illuminate\Http\Response
     */
    public function show(Klamottenboerse $klamottenboerse = NULL)
    {
        if ($klamottenboerse == NULL){
            $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();
        }

        return view('klamottenboerse.grunddaten',[
            "klamottenboerse"   => $klamottenboerse
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\klamottenboerse  $klamottenboerse
     * @return \Illuminate\Http\Response
     */
    public function edit(klamottenboerse $klamottenboerse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\klamottenboerse  $klamottenboerse
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateKlamottenboerseRequest $request, klamottenboerse $klamottenboerse)
    {
        $klamottenboerse->fill($request->all());
        $klamottenboerse->save();

        return redirect()->back()->with([
           "success"    => "Daten gespeichert."
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\klamottenboerse  $klamottenboerse
     * @return \Illuminate\Http\Response
     */
    public function destroy(klamottenboerse $klamottenboerse)
    {
        //
    }
}
