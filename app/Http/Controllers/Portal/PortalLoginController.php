<?php

namespace App\Http\Controllers\Portal;

use App\Domain\Kommunikation\Postausgang;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Verkäufer und Helfer melden sich ohne Passwort über einen signierten Link aus der Mail an.
 * Diese Anmeldung öffnet nur das Portal, nie das Orga-Backend (siehe NurOrga).
 */
class PortalLoginController extends Controller
{
    public function login(Request $request, Person $person): RedirectResponse
    {
        if (Auth::id() !== $person->id) {
            Auth::login($person);
            $request->session()->regenerate();
            $request->session()->put('login_art', 'link');
        }

        $person->forceFill(['letzte_aktivitaet_at' => now(), 'email_verified_at' => $person->email_verified_at ?? now()])->save();

        return redirect()->route('portal.index');
    }

    public function create(): View
    {
        return view('portal.link');
    }

    public function store(Request $request): RedirectResponse
    {
        $daten = $request->validate(['email' => ['required', 'email']]);

        $person = Person::query()->where('email', $daten['email'])->first();
        if ($person) {
            $nachricht = Postausgang::einplanen($person, 'login_link');
            if ($nachricht) {
                Postausgang::senden($nachricht);
            }
        }

        // Immer dieselbe Antwort, damit niemand ausprobieren kann, welche Adressen registriert sind.
        return back()->with('erfolg', 'Wenn die Adresse bei uns bekannt ist, haben wir dir gerade einen Link geschickt.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('start')->with('erfolg', 'Du bist abgemeldet.');
    }
}
