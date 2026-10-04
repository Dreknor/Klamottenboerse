<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend nur für Orga/Admin – und nur nach Login mit Passwort.
 * Ein Magic-Link aus einer Mail öffnet ausschließlich das Portal, nie das Backend.
 */
class NurOrga
{
    public function handle(Request $request, Closure $next): Response
    {
        $person = $request->user();

        if (! $person || ! $person->istOrga()) {
            abort(403, 'Dieser Bereich ist nur für das Orga-Team.');
        }

        if ($request->session()->get('login_art') !== 'passwort') {
            return redirect()->route('login')->with('hinweis', 'Bitte melde dich für das Orga-Backend mit deinem Passwort an.');
        }

        return $next($request);
    }
}
