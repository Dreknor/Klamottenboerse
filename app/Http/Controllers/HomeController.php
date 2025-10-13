<?php

namespace App\Http\Controllers;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\verkaufteartikel;
use App\Model\VKnummer;
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

        if (auth()->user()->verwaltung == 1) {
            $Interessenten = \Cache::remember('interessenten_anzahl', 5, function () {
                return Interessenten::query()->count();
            });
            $Klamottenboerse = Klamottenboerse::query()
                ->withCount('vknummern_vergeben')
                ->withCount('verkaufteArtikel')
                ->orderBy('datum')
                ->get();

            // Statistik als Collection initialisieren, damit sie später sortierbar ist
            $statistik = collect();

            foreach ($Klamottenboerse as $kb) {
                if ($kb->vknummern_vergeben_count > 0 and $kb->vknummern_vergeben->sum('umsatz')) {
                    $statistik->push([
                        'datum' => $kb->datum,
                        'anmeldungen' => $kb->vknummern_vergeben_count,
                        'verkaufteArtikel' => $kb->verkaufte_artikel_count,
                        'umsatz' => $kb->vknummern_vergeben->sum('umsatz'),
                        'maxTeile' => $kb->maxTeile
                    ]);
                }

            }

            //dd($statistik);


            $Mails = $this->imapRepository->mailsInboxLastDays(10);


            return view('home', [
                'Interessenten' => $Interessenten,
                "MailsCount"    => $Mails->count(),
                "unreadMails"   => $Mails->where('flags.seen',0)->count(),
                "Mails" => $Mails->sortByDesc('date')->paginate(10),
                'messages'   => $Mails,
                'Klamottenboersen'   => $Klamottenboerse,
                'statistik' => $statistik,
            ]);
        } elseif (auth()->user()->kasse == 1) {
            return redirect(url('/kasse'));

        } else {
            return redirect()->route('login');
        }
    }
}
