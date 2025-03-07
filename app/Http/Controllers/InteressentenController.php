<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInteressentenRequest;
use App\Http\Requests\UpdateInteressentRequest;
use App\Model\Interessenten;
use App\Model\Mailvorlagen;
use App\Repositories\Interessenten\InteressentenRepository;
use App\Repositories\Mails\ImapRepository;
use App\Repositories\Mails\MailRepository;
use Illuminate\Http\Request;

class InteressentenController extends Controller
{
    public function __construct(InteressentenRepository $interessentenRepository, MailRepository $mailRepository, ImapRepository $imapRepository)
    {
        $this->interessentenRepository = $interessentenRepository;
        $this->mailRepository = $mailRepository;
        $this->imapRepository = $imapRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {


        $Interressenten = $this->interessentenRepository->all();
        $Interressenten->load('warteliste');

        return view('interessenten.uberblick', [
            'interessenten' => $Interressenten,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (auth()->user()->verwaltung != 1) {
            return redirect()->back()->with('error', 'Berechtigung fehlt');
        }


        $mail = \request()->input('email');
        $name = \request()->input('personal');

        return view('interessenten.create', [
            'mail'  => (isset($mail)) ? $mail : null,
            'name'  => (isset($name)) ? $name : null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateInteressentenRequest $request)
    {
        $Interessent = new  Interessenten($request->all());
        $Interessent->save();

        return redirect(url('interessent/'.$Interessent->id));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Interessenten  $interessenten
     * @return
     */
    public function show(Interessenten $interessent, $mailbox='INBOX')
    {
        $interessent->load('bisherige_vknummen', 'bisherige_vknummen.klamottenboerse', 'bisherige_vknummen.aktuelleKlamottenboerse');

        $letzteVKnummern = $interessent->bisherige_vknummen;

        $grouped = $letzteVKnummern->groupBy('vknummer');

        $haeufigsteVKnummer = $grouped->sortByDesc((function ($vknummer, $key) {
            return count($vknummer);
        }));

        if ($interessent->mail) {
            $email = $interessent->mail;
            try {
                $Mails = $this->imapRepository->findMailsOfEMail($email, $mailbox);
            } catch (\Exception $exception) {
                $Mails = [];
            }

        } else {
            $Mails = [];
        }

        return view('interessenten.show', [
            'interessent'   => $interessent,
            'haeufigsteVKnummer'  => $haeufigsteVKnummer,
            'letzteVKnummer'     =>$letzteVKnummern->first(),
            'Vorlagen'      => Mailvorlagen::all(),
            'messages'   =>$Mails,
            'mail_thread' => ($mailbox=='INBOX')? false : true
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
        if ($request->input('kinderhaus') == 'on') {
            $interessenten->kinderhaus = 1;
        } else {
            $interessenten->kinderhaus = 0;
        }

        if ($request->input('mitarbeiter') == 'on') {
            $interessenten->mitarbeiter = 1;
        } else {
            $interessenten->mitarbeiter = 0;
        }

        try {
            $interessenten->save();

            return response()->json($interessenten, 200);
        } catch (\Exception $exception) {
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
        try {
            $this->mailRepository->sendDeleteInteressent($interessenten);

            $interessenten->delete();

            return redirect(url('/'))->with([
                'success'  => 'Interessent wurde gelöscht und informiert.',
            ]);
        } catch (\Exception $exception) {
            dd($exception);

            return redirect()->back()->with([
                'error'  => 'Löschen fehlgeschlagen',
            ]);
        }
    }
}
