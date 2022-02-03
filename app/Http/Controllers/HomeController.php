<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Repositories\Mails\ImapRepository;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ImapRepository $imapRepository)
    {
        $this->middleware('auth');
        $this->imapRepository = $imapRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Interessenten = Interessenten::all();
        //$Mails = $this->imapRepository->mailsInboxLastDays(10);
        $Klamottenboerse = Klamottenboerse::with('vknummern', 'vknummern_vergeben')->get();

        //dd($Klamottenboerse->last());
        return view('home', [
            'Interessenten' => $Interessenten,
            //"MailsCount"    => $Mails->count(),
            //"unreadMails"   => $Mails->where('flags.seen',0)->count(),
            //"Mails" => $Mails->sortByDesc('date')->paginate(10),
            'Klamottenboersen'   => $Klamottenboerse,
            'VKnummern'     => $Klamottenboerse->last()->vknummern,
        ]);
    }
}
