<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicInteressentenRegistrationRequest;
use App\Mail\InteressentRegistrierungBestaetigen;
use App\Model\Interessenten;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PublicRegistrationController extends Controller
{
    public function create()
    {
        return view('registrierung.create', [
            'formRenderedAt' => time(),
        ]);
    }

    public function store(PublicInteressentenRegistrationRequest $request)
    {
        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => $request->input('anrede'),
            'vorname' => $request->input('vorname'),
            'nachname' => $request->input('nachname'),
            'mail' => $request->input('mail'),
            'telefon' => $request->input('telefon'),
            'handy' => $request->input('handy'),
            'registration_source' => 'self-service',
        ]);

        $verificationUrl = URL::temporarySignedRoute(
            'registrierung.bestaetigen',
            now()->addHours(48),
            ['interessent' => $interessent->id]
        );

        Mail::to($interessent->mail)->send(
            new InteressentRegistrierungBestaetigen($interessent, $verificationUrl)
        );

        return redirect()
            ->route('registrierung.create')
            ->with('success', 'Bitte bestätige deine Registrierung über den Link in der E-Mail, die wir dir gerade gesendet haben.');
    }

    public function confirm(Request $request, Interessenten $interessent)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Der Bestätigungslink ist ungültig oder abgelaufen.');
        }

        if (! $interessent->hasVerifiedEmail()) {
            $interessent->markEmailAsVerified();
        }

        return view('registrierung.bestaetigt', [
            'interessent' => $interessent,
        ]);
    }
}
