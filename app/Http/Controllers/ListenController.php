<?php

namespace App\Http\Controllers;

use App\Model\VKnummer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ListenController extends Controller
{
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->klamottenboersenRepository = $klamottenboersenRepository;
    }

    public function verkaeuferinfos()
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf = $pdf->loadView('pdf.verkaeuferinfos', [
            'Klamottenboerse'   => $this->klamottenboersenRepository->aktuelleKlamottenboerse(),
        ]);

        $pdf->save(storage_path().'/Verkaeuferinfos.pdf');

        return $pdf->stream('Verkaeuferinfos.pdf');
    }

    public function vknummern()
    {
        $Klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        $pdf = App::make('dompdf.wrapper');
        $pdf = $pdf->loadView('pdf.vknummern', [
            'Klamottenboerse'   => $Klamottenboerse,
            'Nummern'           => $Klamottenboerse->vknummern_vergeben,
        ]);

        $pdf->save(storage_path().'/vknummern.pdf');

        return $pdf->stream('vknummern.pdf');
    }

    public function belehrung($vknummer = '')
    {
        $Klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        if (! $vknummer) {
            $VKnummer = $Klamottenboerse->vknummern_vergeben;
        } else {
            $VKnummer = collect(VKnummer::with('vergeben_an_Interessent')->where('vknummer', $vknummer)->where('klamottenboersen_id', $Klamottenboerse->id)->get());
        }

        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4');
        $pdf = $pdf->loadView('pdf.belehrung', [
            'Klamottenboerse'   => $Klamottenboerse,
            'Nummern'           => $VKnummer,
            'belehrung'         => $Klamottenboerse->belehrung
        ]);

        $pdf->save(storage_path().'/belehrung.pdf');

        return $pdf->stream('belehrung.pdf');
    }

    public function abstreichliste()
    {
        $Klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        $pdf = App::make('dompdf.wrapper');
        $pdf = $pdf->loadView('pdf.abstreichliste', [
            'Klamottenboerse'   => $Klamottenboerse,
            'Nummern'           => $Klamottenboerse->vknummern_vergeben,
        ]);

        $pdf->save(storage_path().'/abstreichliste.pdf');

        return $pdf->stream('abstreichliste.pdf');
    }
}
