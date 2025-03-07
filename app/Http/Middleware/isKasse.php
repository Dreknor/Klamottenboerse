<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class isKasse
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

        if(!Auth::check()){
            return redirect()->route('login');
        }

        if(Auth::user()->isKasse == 1){
            return $next($request);
        }

        return redirect(url('/'))->with('unauthorised', 'You are
                  unauthorised to access this page');
    }
}
