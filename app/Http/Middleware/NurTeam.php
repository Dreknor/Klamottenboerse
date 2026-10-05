<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bereiche für alle Team-Mitglieder – egal welche Rolle (Admin, Orga, Kasse, Annahme):
 * Aufgaben, Kalender, Protokolle, Ablage, Auswertung. Nur nach Login mit Passwort.
 */
class NurTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        $person = $request->user();

        if (! $person || ! $person->istTeam()) {
            abort(403, 'Dieser Bereich ist nur für das Team.');
        }

        if ($request->session()->get('login_art') !== 'passwort') {
            return redirect()->route('login')->with('hinweis', 'Bitte melde dich mit deinem Passwort an.');
        }

        return $next($request);
    }
}
