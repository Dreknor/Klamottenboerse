<?php

namespace App\Http\Controllers;


use App\Http\Requests\ReserviereNummerRequest;
use App\Mail\Nummerentzogen;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Repositories\Nummern\VKnummerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use phpDocumentor\Reflection\DocBlock;

class NummernController extends Controller
{
    public function __construct(VKnummerRepository $VKnummerRepository)
    {
        $this->nummernRepository = $VKnummerRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Klamottenboerse = Klamottenboerse::query()->latest()->with('vknummern', 'vknummern.vergeben_an_Interessent', 'vknummern.reserviert_fuer_Interessent', 'vknummern.bisherigeVerkaeufer')->first();
        return view('vknummern.index',[
           "vknummern"  => $Klamottenboerse->vknummern
        ]);
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
     * @param  \App\Model\  $nummernController
     * @return \Illuminate\Http\Response
     */
    public function show(VKnummer $VKnummern)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(VKnummer $VKnummer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VKnummer $VKnummer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(VKnummer $VKnummer)
    {
        //
    }

    public function reservierungAufheben (VKnummer $id){
        try {
            $id->update([
                "reserviert_fuer"   => NULL
            ]);
            $id->save();

            return redirect()->back()->with([
                "success" => "Reservierung wurde aufgehoben."
            ]);
        } catch (\Exception $exception){

            return redirect()->back()->with([
                "fehler" => "Reservierung konnte nicht aufgehoben werden."
            ]);
        }
    }

    public function reserviereNummer(Interessenten $interessenten){

        $Nummern=$this->nummernRepository->freeNummern();
        //dd($Nummern);

        return view('vknummern.reserviereNummer',[
            "Interessent"   => $interessenten,
            "Nummern"       => $Nummern->load('bisherigeVerkaeufer')
        ]);
    }

    public function nummerReservieren(ReserviereNummerRequest $reserviereNummerRequest){

        try{
            $Nummer = VKnummer::find($reserviereNummerRequest->input('NummernID'));
            $Nummer->reserviert_fuer = $reserviereNummerRequest->input('interessent');
            $Nummer->save();

            return redirect(url('interessent/'.$reserviereNummerRequest->input('interessent')))->with([
                "success" => "Nummer reserviert"
            ]);
        } catch (\Exception $exception){

            dd($exception);
            return redirect(url('interessent/'.$reserviereNummerRequest->input('interessent')))->with([
                "error" => "Nummer konnte nicht reserviert werden."
            ]);
        }

    }

    public function reservierungVergeben(VKnummer $vknummer){

        try{

            $vknummer->vergeben_an = $vknummer->reserviert_fuer;
            $vknummer->save();

            return redirect()->back()->with([
               "success"  => "Nummer vergeben und Interessent informiert."
            ]);
        } catch (\Exception $exception){
            return redirect()->back()->with([
               "error"  => "Nummer konnte nicht vergeben werden."
            ]);
        }
    }

    public function removeVergabe(VKnummer $vknummer){

        try{

            $Interessent = $vknummer->vergeben_an_Interessent;
            $vknummer->vergeben_an = NULL;
            $vknummer->save();

            Mail::to($Interessent->mail)->send(new Nummerentzogen($Interessent));


            return redirect()->back()->with([
                "success"  => "Vergabe wurde aufgehoben und Interessent informiert."
            ]);
        } catch (\Exception $exception){
            return redirect()->back()->with([
                "error"  => "Vergabe konnte nicht aufgehoben werden."
            ]);
        }
    }
}
