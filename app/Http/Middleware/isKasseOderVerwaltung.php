<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Zugriff für Kassen- und Verwaltungs-Accounts, z. B. für die
 * Schnellerfassung von Verkäufer-Vermerken direkt von der Kasse aus.
 */
class isKasseOderVerwaltung
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Gate::none(['access-kasse', 'access-verwaltung'], $request->user())) {
            return redirect(url('/'))->with('danger', 'You are unauthorised to access this page');
        }

        return $next($request);
    }
}
