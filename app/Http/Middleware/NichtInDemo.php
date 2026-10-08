<?php

namespace App\Http\Middleware;

use App\Support\Demo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sperrt Aktionen in der Demo, die nach außen wirken oder andere Testende aussperren würden
 * (Passwörter, Updates, Push-Nachrichten).
 */
class NichtInDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Demo::aktiv()) {
            return $next($request);
        }

        $meldung = 'Das ist in der Demo abgeschaltet – in der echten Installation funktioniert es.';

        return $request->expectsJson()
            ? response()->json(['message' => $meldung], 403)
            : back()->with('fehler', $meldung);
    }
}
