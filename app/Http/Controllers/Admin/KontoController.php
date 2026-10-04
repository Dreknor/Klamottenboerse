<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** "Mein Konto": eigenes Passwort ändern. */
class KontoController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.konto', ['person' => $request->user()]);
    }

    public function passwort(Request $request): RedirectResponse
    {
        $request->validate([
            'aktuelles_passwort' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(10)],
        ], ['aktuelles_passwort.current_password' => 'Das aktuelle Passwort stimmt nicht.']);

        $request->user()->update(['password' => $request->input('password')]);
        activity()->performedOn($request->user())->log('Passwort geändert');

        return back()->with('erfolg', 'Passwort geändert.');
    }
}
