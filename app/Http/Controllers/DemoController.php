<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Support\Demo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Nur in der Demo: Anmelden per Klick in einer Rolle und Zurücksetzen der Beispieldaten. */
class DemoController extends Controller
{
    public function anmelden(Request $request, string $rolle): RedirectResponse
    {
        abort_unless(Demo::aktiv() && isset(Demo::ZUGAENGE[$rolle]), 404);

        $person = Person::query()->where('email', Demo::ZUGAENGE[$rolle][0])->firstOrFail();
        Auth::login($person);
        $request->session()->regenerate();
        // Verkäuferin wie nach einem Mail-Link (nur Portal), das Team wie mit Passwort
        $request->session()->put('login_art', $rolle === 'verkaeufer' ? 'link' : 'passwort');

        return match ($rolle) {
            'admin', 'orga' => redirect()->route('admin.dashboard'),
            'kasse' => redirect()->route('kasse.index'),
            'annahme' => redirect()->route('tablet.index'),
            default => redirect()->route('portal.index'),
        };
    }

    public function zuruecksetzen(): RedirectResponse
    {
        abort_unless(Demo::aktiv(), 404);

        Demo::zuruecksetzen(); // leert auch die Sitzungen – danach neu anmelden

        return redirect()->route('login')->with('erfolg', 'Die Demo ist zurückgesetzt. Bitte neu anmelden.');
    }
}
