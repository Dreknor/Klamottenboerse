<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInteressentRequest;
use App\Model\Interessenten;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Mails\ImapRepository;
use Illuminate\Http\Request;

class InteressentenController extends Controller
{
    public function __construct(InteressentenRepository $interessentenRepository)
    {
        $this->interessentenRepository = $interessentenRepository;

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('interessenten.uberblick',[
            "interessenten" =>  $this->interessentenRepository->all()
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
     * @param  \App\Model\Interessenten  $interessenten
     * @return \Illuminate\Http\Response
     */
    public function show(Interessenten $interessent)
    {


        return view('interessenten.show',[
            "interessent"   => $interessent->load('bisherige_vknummen', 'bisherige_vknummen.klamottenboerse')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Model\Interessenten  $interessenten
     * @return \Illuminate\Http\Response
     */
    public function edit(Interessenten $interessenten)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Interessenten  $interessenten
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateInteressentRequest $request, Interessenten $interessenten)
    {
        $interessenten->fill($request->all());
        if ($request->input('kinderhaus') == "on"){
            $interessenten->kinderhaus = 1;
        } else {
            $interessenten->kinderhaus = 0;
        }

        if ($request->input('mitarbeiter') == "on"){
            $interessenten->mitarbeiter = 1;
        } else {
            $interessenten->mitarbeiter = 0;
        }

        try{
            $interessenten->save();
            return response()->json($interessenten, 200);
        } catch (\Exception $exception){
            return response()->json($exception, 400);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Interessenten  $interessenten
     * @return \Illuminate\Http\Response
     */
    public function destroy(Interessenten $interessenten)
    {
        //
    }
}
