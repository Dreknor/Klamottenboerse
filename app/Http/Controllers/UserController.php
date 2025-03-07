<?php

namespace App\Http\Controllers;

use App\Mail\sendPasswordMail;
use App\Model\Interessenten;
use App\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function delete (Interessenten $interessenten){
        if (auth()->id() == $interessenten->id){
            return redirect()->back()->with([
                'fehler'   => 'Eigener Zugang kann nicht gelöscht werden'
            ]);
        }

        $interessenten->user()->delete();

        return redirect()->back()->with([
            'success'   => 'Zugang wurde gelöscht'
        ]);

    }

    public function create (Interessenten $interessenten){

        if ($interessenten->mitarbeiter != "ja"){
            return redirect()->back()->with([
                'fehler'   => 'Zugang darf nur für Mitarbeiter erstellt werden'
            ]);
        }

        $password = Str::random(8);

        if ($interessenten->user_id != null){
            $user = User::where('id', $interessenten->user_id)->withTrashed()->first();
            if (!is_null($user)){
                $user->restore();
                $user->update([
                    'password'   =>Hash::make($password),
                    'verwaltung'  => 1
                ]);

            }
        } else {
            $user=User::firstOrCreate([
                'email'   => $interessenten->mail
            ], [
                'name'   =>$interessenten->vorname .' '. $interessenten->nachname,
                'password'   =>Hash::make($password),
                'verwaltung'  => 1
            ]);

            $interessenten->update([
                'user_id' => $user->id,
            ]);

        }


        Mail::to($interessenten->mail)->send(new sendPasswordMail($interessenten, $password));



        return redirect()->back()->with([
            'success'   => 'Zugang wurde erstellt'
        ]);

    }

    public function createKasseZugang(Interessenten $interessenten){

        if ($interessenten->mitarbeiter != "ja"){
            return redirect()->back()->with([
                'fehler'   => 'Zugang darf nur für Mitarbeiter erstellt werden'
            ]);
        }

        $password = Str::random(8);

        if ($interessenten->user_id != null){
            $user = User::where('id', $interessenten->user_id)->withTrashed()->first();
            if (!is_null($user)){
                $user->restore();
                $user->update([
                    'password'   =>Hash::make($password),
                    'kasse'  => 1
                ]);

            }
        } else {
            $user=User::firstOrCreate([
                'email'   => $interessenten->mail
            ], [
                'name'   =>$interessenten->vorname .' '. $interessenten->nachname,
                'password'   =>Hash::make($password),
                'kasse'  => 1
            ]);

            $interessenten->update([
                'user_id' => $user->id,
            ]);

        }
    }

    public function removeKassenZugang(Interessenten $interessenten)
    {
        if (auth()->id() == $interessenten->id){
            return redirect()->back()->with([
                'fehler'   => 'Eigener Zugang kann nicht gelöscht werden'
            ]);
        }

        $interessenten->user()->update([
            'kasse' => 0
        ]);

        if ($interessenten->kasse == 0 and $interessenten->verwaltung == 0){
            $interessenten->user()->delete();
        } else {
            return redirect()->back()->with([
                'success'   => 'Zugang zur Kasse wurde gelöscht'
            ]);
        }



        return redirect()->back()->with([
            'success'   => 'Zugang wurde gelöscht'
        ]);
    }
}
