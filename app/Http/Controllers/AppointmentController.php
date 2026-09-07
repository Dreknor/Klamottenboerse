<?php

namespace App\Http\Controllers;

use App\Http\Requests\createAppointmentRequest;
use App\Http\Requests\createHelferRequest;
use App\Model\Appointment;
use App\Model\Helfer;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class AppointmentController extends Controller
{
    public function __construct(KlamottenboersenRepository $klamottenboersenRepository)
    {
        $this->klamottenboersenRepository = $klamottenboersenRepository;
    }

    public function storeHelfer(createHelferRequest $request)
    {



        $helfer = new Helfer();
        $helfer->name = $request->name;
        $helfer->mail = $request->mail;
        $helfer->telefon = $request->telefon;
        $helfer->appointment_id = $request->termin;
        $helfer->bereich = "Helfer";
        $helfer->klamottenboerse_id = $this->klamottenboersenRepository->aktuelleKlamottenboerse()->id;
        $helfer->save();

        $appointment = Appointment::find($request->termin);
        $appointment->helfer_id = $helfer->id;
        $appointment->save();

        return redirect()->back()->with('success', 'Vielen Dank, dass du dich als Helfer eingetragen hast');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        return \view(
            'helferliste',
            [
                'termine' => Appointment::where('klamottenboerse_id', $this->klamottenboersenRepository->aktuelleKlamottenboerse()->id)->where('helfer_id', null)->orderBy('date_start')->get(),
                'klamottenboerse' => $this->klamottenboersenRepository->aktuelleKlamottenboerse()
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {


        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        return view('klamottenboerse.helfer',
            [
                'termine' => Appointment::where('klamottenboerse_id', $klamottenboerse->id)->orderBy('date_start')->get(),
                'helfer' => Helfer::where('klamottenboerse_id', $klamottenboerse->id)->get()
            ]
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(createAppointmentRequest $request)
    {

        if (auth()->user()->verwaltung != 1) {
            return redirect()->back()->with('error', 'Berechtigung fehlt');
        }

        $klamottenboerse = $this->klamottenboersenRepository->aktuelleKlamottenboerse();

        for ($i = 0; $i < $request->anzahl; $i++) {
            $appointment = new Appointment();
            $appointment->klamottenboerse_id = $klamottenboerse->id;
            $appointment->beschreibung = $request->beschreibung;
            $appointment->bereich = $request->bereich;
            $appointment->date_start = $request->date_start;
            $appointment->date_end = $request->date_end;
            $appointment->save();
        }

        return redirect()->route('helfertermine');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Appointment $appointment)
    {
        if (auth()->user()->verwaltung != 1) {
            return redirect()->back()->with('error', 'Berechtigung fehlt');
        }

        $appointment->helfer()->delete();
        $appointment->delete();

        return redirect()->route('helfertermine')->with('success', 'Termin gelöscht');
    }
}
