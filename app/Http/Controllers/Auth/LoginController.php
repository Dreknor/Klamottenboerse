<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/** Login mit Passwort für Orga-Team, Kasse und Annahme. */
class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::validate($daten)) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'E-Mail oder Passwort stimmen nicht.']);
        }

        return $this->anmelden($request, Auth::getProvider()->retrieveByCredentials(['email' => $daten['email']]), $request->boolean('merken'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('start');
    }

    private function anmelden(Request $request, Person $person, bool $merken): RedirectResponse
    {
        Auth::login($person, $merken);
        $request->session()->regenerate();
        $request->session()->put('login_art', 'passwort');
        $person->forceFill(['letzte_aktivitaet_at' => now()])->save();

        return match (true) {
            $person->istOrga() => redirect()->intended(route('admin.dashboard')),
            $person->hasRole('kasse') => redirect()->route('kasse.index'),
            $person->hasRole('annahme') => redirect()->route('tablet.index'),
            default => redirect()->route('portal.index'),
        };
    }
}
