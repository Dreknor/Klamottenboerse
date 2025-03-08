<?php

namespace App\Http\Controllers;

use App\Http\Requests\neueKlamottenboerseRequest;
use App\Http\Requests\UpdateKlamottenboerseRequest;
use App\Imports\vknummernImport;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
    public function import()
    {
        return view('settings.upload');
    }

    public function saveImport(Request $request)
    {

        //$Excel = Excel::import(new vknummernImport(), request()->file('import'));
        $collection = Excel::import(new vknummernImport(), request()->file('import'));

        return redirect(url('home'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('klamottenboerse.neueKlamottenboerse', [
            'klamottenboerse'    => $this->klamottenboersenRepository->aktuelleKlamottenboerse(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(neueKlamottenboerseRequest $request)
    {
        $VKnummern = $this->klamottenboersenRepository->aktuelleKlamottenboerse()->vknummern;

        $old = $this->klamottenboersenRepository->aktuelleKlamottenboerse();
        $Klamottenboerse = new Klamottenboerse($request->validated());
        $Klamottenboerse->ort = $old->ort;
        $Klamottenboerse->adresse = $old->adresse;
        $Klamottenboerse->belehrung = $old->belehrung;
        $Klamottenboerse->save();

        $data = [];
        foreach ($VKnummern as $Nummer) {
            $data[] = ['vknummer' => $Nummer->vknummer, 'klamottenboersen_id'=>$Klamottenboerse->id, 'reserviert_fuer' => $Nummer->reserviert_fuer];
        }

        VKnummer::insert($data);

        return redirect(url('klamottenboerse/'.$Klamottenboerse->id))->with([
            'success'    => 'Klamottenbörse angelegt.',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\klamottenboerse  $klamottenboerse
     * @return \Illuminate\Http\Response
     */
    public function show(Klamottenboerse $klamottenboerse = null)
    {
        if ($klamottenboerse == null) {
            $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();
        }

        return view('klamottenboerse.grunddaten', [
            'klamottenboerse'   => $klamottenboerse,
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
            'success'    => 'Daten gespeichert.',
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
