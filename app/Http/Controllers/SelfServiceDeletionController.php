<?php

namespace App\Http\Controllers;

use App\Mail\InteressentLoeschungBestaetigen;
use App\Model\Interessenten;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

class SelfServiceDeletionController extends Controller
{
    public function create()
    {
        return view('registrierung.loeschen');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mail' => 'required|email',
        ]);

        $interessent = Interessenten::where('mail', $request->input('mail'))->first();

        // Aus Datenschutzgründen wird unabhängig vom Ergebnis dieselbe
        // Erfolgsmeldung angezeigt (kein Enumerieren registrierter Mailadressen möglich).
        if ($interessent) {
            $confirmationUrl = URL::temporarySignedRoute(
                'registrierung.loeschung.bestaetigen',
                now()->addHours(48),
                ['interessent' => $interessent->id]
            );

            Mail::to($interessent->mail)->send(
                new InteressentLoeschungBestaetigen($interessent, $confirmationUrl)
            );
        }

        return redirect()
            ->route('registrierung.loeschen')
            ->with('success', 'Falls diese E-Mail-Adresse registriert ist, erhältst du in Kürze eine Bestätigungs-Mail.');
    }

    public function confirm(Request $request, Interessenten $interessent)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Der Bestätigungslink ist ungültig oder abgelaufen.');
        }

        $interessent->deletion_requested_at = now();
        $interessent->save();
        $interessent->delete(); // Soft-Delete (Schutzfrist von 30 Tagen bis zur endgültigen Löschung)

        AuditLogger::log('interessent.selbstloeschung_angefragt', $interessent, [
            'mail' => $interessent->mail,
        ]);

        return view('registrierung.geloescht', [
            'interessent' => $interessent,
        ]);
    }
}
