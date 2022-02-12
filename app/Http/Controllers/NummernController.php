<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReserviereNummerRequest;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Repositories\Mails\MailRepository;
use App\Repositories\Nummern\VKnummerRepository;
use Illuminate\Http\Request;

class NummernController extends Controller
{
    public function __construct(VKnummerRepository $VKnummerRepository, MailRepository $mailRepository)
    {
        $this->nummernRepository = $VKnummerRepository;
        $this->mailRepository = $mailRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Klamottenboerse = Klamottenboerse::query()->latest()->with('vknummern', 'vknummern.vergeben_an_Interessent', 'vknummern.reserviert_fuer_Interessent', 'vknummern.bisherigeVerkaeufer')->first();

        return view('vknummern.index', [
            'vknummern'  => $Klamottenboerse->vknummern,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('vknummern.newVKnummer');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $Klamottenboerse = Klamottenboerse::query()->latest()->first();
        $Nummmer = VKnummer::firstOrCreate([
            'vknummer'  => $request->input('vknummer'),
            'klamottenboersen_id'   => $Klamottenboerse->id,
        ]);

        return redirect(url('vknummern'));
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

    public function reservierungAufheben(VKnummer $id)
    {
        try {
            $id->update([
                'reserviert_fuer'   => null,
            ]);
            $id->save();

            return redirect()->back()->with([
                'success' => 'Reservierung wurde aufgehoben.',
            ]);
        } catch (\Exception $exception) {
            return redirect()->back()->with([
                'fehler' => 'Reservierung konnte nicht aufgehoben werden.',
            ]);
        }
    }

    public function reserviereNummer(Interessenten $interessenten)
    {
        $Nummern = $this->nummernRepository->freeNummern();

        return view('vknummern.reserviereNummer', [
            'Interessent'   => $interessenten,
            'Nummern'       => $Nummern->load('bisherigeVerkaeufer'),
        ]);
    }

    public function nummerReservieren(ReserviereNummerRequest $reserviereNummerRequest)
    {
        try {
            $Nummer = VKnummer::find($reserviereNummerRequest->input('NummernID'));
            $Nummer->reserviert_fuer = $reserviereNummerRequest->input('interessent');
            $Nummer->save();

            return redirect(url('interessent/'.$reserviereNummerRequest->input('interessent')))->with([
                'success' => 'Nummer reserviert',
            ]);
        } catch (\Exception $exception) {
            return redirect(url('interessent/'.$reserviereNummerRequest->input('interessent')))->with([
                'error' => 'Nummer konnte nicht reserviert werden.',
            ]);
        }
    }

    public function reservierungVergeben(VKnummer $vknummer)
    {
        $vknummer->vergeben_an = $vknummer->reserviert_fuer;
        $vknummer->save();

        if ($vknummer->vergeben_an_Interessent->has('warteliste') and $vknummer->vergeben_an_Interessent->warteliste != null) {
            $vknummer->vergeben_an_Interessent->warteliste->delete();
        }

        $this->mailRepository->sendVerkaeuferInfo($vknummer);

        return redirect()->back()->with([
            'success'  => 'Nummer vergeben und Interessent informiert.',
        ]);
    }

    public function removeVergabe(VKnummer $vknummer)
    {
        try {
            $Interessent = $vknummer->vergeben_an_Interessent;
            $vknummer->vergeben_an = null;
            $vknummer->save();

            $this->mailRepository->sendRuecknahmeNummer($Interessent);

            //Mail::to($Interessent->mail)->send(new Nummerentzogen($Interessent));

            return redirect()->back()->with([
                'success'  => 'Vergabe wurde aufgehoben und Interessent informiert.',
            ]);
        } catch (\Exception $exception) {
            return redirect()->back()->with([
                'error'  => 'Vergabe konnte nicht aufgehoben werden.',
            ]);
        }
    }

    public function vergebeNummer(Interessenten $interessenten)
    {
        $Nummern = $this->nummernRepository->freeNummern();
        $Nummern = $Nummern->sortBy('vknummer');
        //$interessenten = Interessenten::find($interessenten);

        return view('vknummern.vergebeNummer', [
            'Interessent'   => $interessenten,
            'Nummern'       => $Nummern->load('bisherigeVerkaeufer'),
        ]);
    }

    public function newVKnummerVergeben(Request $request)
    {
        $VKnummer = VKnummer::findOrFail($request->input('NummernID'));
        $VKnummer->vergeben_an = $request->input('InteressentID');
        $VKnummer->save();

        if ($VKnummer->vergeben_an_Interessent->has('warteliste') and $VKnummer->vergeben_an_Interessent->warteliste != null) {
            $VKnummer->vergeben_an_Interessent->warteliste->delete();
        }
        $this->mailRepository->sendVerkaeuferInfo($VKnummer);

        return redirect(url('interessent/'.$request->input('InteressentID')))->with([
            'success'  => 'Nummer vergeben und Interessent informiert.',
        ]);
    }

    public function freiVergeben(VKnummer $vknummer)
    {
        return view('vknummern.vergebeVKnummerUebersicht', [
            'Nummer' => $vknummer,
            'Interessenten' => Interessenten::doesntHave('vknummern_vergeben')->orderBy('nachname')->get(),
        ]);
    }
}
