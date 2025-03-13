<?php

namespace App\Http\Controllers;

use App\Mail\ErgebnisLinkMail;
use App\Model\Interessenten;
use App\Model\settings;
use App\Repositories\Klamottenboerse\KlamottenboersenRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ErgebnisController extends Controller
{

    public function __construct()
    {

    }

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

        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $interessent = Interessenten::query()->where('mail', $request->input('email'))->first();
            if ($interessent){
                Mail::to($interessent->mail)->send(new ErgebnisLinkMail($interessent));
            }
        } catch (\Exception $e) {
            return redirect()->route('ergebnis')->with([
                'message' => 'Es ist ein Fehler aufgetreten. Bitte versuchen Sie es später erneut.'
            ]);
        }


        return redirect()->route('ergebnis')->with([
            'message' => 'Sollte die E-Mail-Adresse in unserer Datenbank vorhanden sein, haben wir Ihnen den Link zum Ergebnis zugesendet.'
        ]);

    }

    public function show($uuid) {
        if (!(new KlamottenboersenRepository())->aktuelleKlamottenboerse()->ergebnis_freigabe) {
            abort(404);
        }

        $interessent = Interessenten::query()->where('uuid', $uuid)->first();
        if ($interessent) {
           $vknummer = $interessent->vknummern_vergeben;

           $klamottenboerse = (new KlamottenboersenRepository())->aktuelleKlamottenboerse();

           if ($vknummer && $vknummer->klamottenboersen_id == $klamottenboerse->id) {
             return view('ergebnis.ergebnis', [
                    'interessent' => $interessent,
                    'vknummer' => $vknummer,
                    'klamottenboerse' => $klamottenboerse,
                    'einbehalt' => settings::query()->latest()->first()->provision
             ]);
              } else {
                return redirect()->route('ergebnis')->with([
                    'message' => 'Für diese Klamottenbörse liegt kein Ergebnis vor.'
                ]);
            }

        } else {
            return redirect()->route('ergebnis')->with([
                'message' => 'Der Link ist ungültig.'
            ]);
        }
    }
}
