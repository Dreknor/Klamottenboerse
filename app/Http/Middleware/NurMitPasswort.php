<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Kasse und Tablet nur nach Login mit Passwort – ein Link aus einer Mail reicht nicht. */
class NurMitPasswort
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->get('login_art') !== 'passwort') {
            return redirect()->route('login')->with('hinweis', 'Bitte melde dich mit deinem Passwort an.');
        }

        return $next($request);
    }
}
