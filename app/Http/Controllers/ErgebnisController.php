<?php

namespace App\Http\Controllers;

use App\Mail\ErgebnisLinkMail;
use App\Model\Interessenten;
use App\Model\settings;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Model\VKnummer;

class ErgebnisController extends Controller
{
    public function __construct() { }

    public function index() {
        if (!(new KlamottenboersenRepository())->aktuelleKlamottenboerse()->ergebnis_freigabe) {
            abort(404);
        }
        return view('ergebnis.index');
    }

    public function mail(Request $request) {
        if (!(new KlamottenboersenRepository())->aktuelleKlamottenboerse()->ergebnis_freigabe) {
            abort(404);
        }
        $request->validate(['email' => 'required|email']);
        try {
            $interessent = Interessenten::query()->where('mail', $request->input('email'))->first();
            if ($interessent) {
                Mail::to($interessent->mail)->send(new ErgebnisLinkMail($interessent));
            }
        } catch (\Exception $e) {
            return redirect()->route('ergebnis')->with(['message' => 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.']);
        }
        return redirect()->route('ergebnis')->with(['message' => 'Sollte die E-Mail-Adresse in unserer Datenbank vorhanden sein, haben wir Ihnen den Link zum Ergebnis zugesendet.']);
    }

    public function show($uuid) {

        $interessent = Interessenten::query()->where('uuid', $uuid)->first();
        if (!$interessent) {
            return redirect()->route('ergebnis')->with(['message' => 'Der Link ist ungültig.']);
        }
        // Alle freigegebenen Ergebnisse (auch vergangene) sammeln
        $vknummern = $interessent->bisherige_vknummen()->take(2)->get()->filter(function($vknummer) {;
            return $vknummer->Klamottenboerse && $vknummer->Klamottenboerse->ergebnis_freigabe;
        });



        if ($vknummern->count() > 0) {
            return view('ergebnis.ergebnis', [
                'interessent' => $interessent,
                'vknummernFreigegeben' => $vknummern,
            ]);
        }
        return redirect()->route('ergebnis')->with(['message' => 'Für diese Klamottenbörse liegt kein Ergebnis vor.']);
    }
}
