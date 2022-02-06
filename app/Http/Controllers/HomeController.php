<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Repositories\Mails\ImapRepository;
use Illuminate\View\View;

class HomeController extends Controller
{
    private ImapRepository $imapRepository;

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
     * @return View
     */
    public function index()
    {
        $Interessenten = Interessenten::all();
        $Mails = $this->imapRepository->mailsInboxLastDays(10);
        $Klamottenboerse = Klamottenboerse::all();

        return view('home', [
            'Interessenten' => $Interessenten,
            //"MailsCount"    => $Mails->count(),
            //"unreadMails"   => $Mails->where('flags.seen',0)->count(),
            //"Mails" => $Mails->sortByDesc('date')->paginate(10),
            'messages'   => $Mails,
            'Klamottenboersen'   => $Klamottenboerse,
            'VKnummern'     => $Klamottenboerse->last()->vknummern,
        ]);
    }
}
