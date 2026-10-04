<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Person;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswortRegel;
use Illuminate\View\View;

/** "Passwort vergessen" für das Team. Verkäufer und Helfer brauchen kein Passwort (Magic-Link). */
class PasswortController extends Controller
{
    public function create(): View
    {
        return view('auth.passwort-vergessen');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $person = Person::query()->where('email', $request->input('email'))->first();
        if ($person && $person->roles()->exists()) {
            Password::sendResetLink(['email' => $person->email]);
        }

        // Immer dieselbe Antwort – verrät nicht, welche Adressen ein Konto haben.
        return back()->with('erfolg', 'Wenn zu dieser Adresse ein Team-Zugang existiert, haben wir dir einen Link zum Zurücksetzen geschickt.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.passwort-neu', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswortRegel::min(10)],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function (Person $person, string $passwort) {
            $person->forceFill(['password' => $passwort, 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($person));
            activity()->performedOn($person)->log('Passwort zurückgesetzt');
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('erfolg', 'Dein Passwort ist geändert. Du kannst dich jetzt anmelden.')
            : back()->withInput($request->only('email'))->withErrors(['email' => 'Der Link ist ungültig oder abgelaufen. Bitte fordere einen neuen an.']);
    }
}
