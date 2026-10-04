<?php

namespace App\Http\Controllers\Public;

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use App\Http\Controllers\Controller;
use App\Models\Boerse;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Online-Anmeldung als Verkäufer mit Bestätigung per Mail (Double-Opt-in).
 * Die Nummer wird erst bei der Bestätigung vergeben – wer zuerst bestätigt, ist zuerst dran.
 */
class AnmeldungController extends Controller
{
    public function create(): View
    {
        $boerse = $this->boerse();

        return view('public.anmeldung', [
            'boerse' => $boerse,
            'offenFuerAlle' => $boerse?->anmeldung_ab?->isPast() ?? false,
            'offenFuerKinderhaus' => ($boerse?->anmeldung_kinderhaus_ab ?? $boerse?->anmeldung_ab)?->isPast() ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse|View
    {
        // Honeypot gegen Spam-Bots
        if (filled($request->input('webseite'))) {
            return redirect()->route('start');
        }

        $boerse = $this->boerse() ?? abort(404);

        $daten = $request->validate([
            'vorname' => ['required', 'string', 'max:100'],
            'nachname' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'telefon' => ['nullable', 'string', 'max:50'],
            'kinderhaus_bezug' => ['required', Rule::enum(KinderhausBezug::class)],
            'info_mails' => ['nullable', 'boolean'],
            'datenschutz' => ['accepted'],
        ], ['datenschutz.accepted' => 'Bitte stimme der Verarbeitung deiner Daten zu.']);

        $person = Person::query()->where('email', $daten['email'])->first();
        if (! $person) {
            $person = Person::create([
                'vorname' => $daten['vorname'],
                'nachname' => $daten['nachname'],
                'email' => $daten['email'],
                'telefon' => $daten['telefon'] ?? null,
                'kinderhaus_bezug' => $daten['kinderhaus_bezug'],
            ]);
        } elseif ($person->kinderhaus_bezug === KinderhausBezug::Keiner && $daten['kinderhaus_bezug'] !== KinderhausBezug::Keiner->value) {
            $person->update(['kinderhaus_bezug' => $daten['kinderhaus_bezug']]);
        }

        if ($request->boolean('info_mails') && ! $person->info_mails_erlaubt_at) {
            $person->update(['info_mails_erlaubt_at' => now()]);
        }

        if (! $boerse->anmeldungOffenFuer($person)) {
            return back()->withInput()->with('fehler', 'Die Anmeldung ist für dich noch nicht geöffnet. Bitte schau zum angegebenen Start wieder vorbei.');
        }

        $nachricht = Postausgang::einplanen($person, 'anmeldung_bestaetigen', $boerse, [
            'bestaetigen_link' => URL::temporarySignedRoute('anmeldung.bestaetigen', now()->addDays(2), ['person' => $person->uuid, 'boerse' => $boerse->id]),
        ]);
        if ($nachricht) {
            Postausgang::senden($nachricht); // sofort, nicht über die Drossel
        }

        return view('public.anmeldung-gesendet', ['email' => $person->email]);
    }

    public function bestaetigen(Request $request, Person $person, Anmelden $anmelden): View
    {
        $boerse = Boerse::query()->findOrFail($request->integer('boerse'));
        $person->forceFill(['email_verified_at' => $person->email_verified_at ?? now()])->save();

        $teilnahme = $anmelden($boerse, $person, 'online');

        // Der Link kam per Mail an genau diese Adresse – wie beim Portal-Link direkt anmelden.
        if (! Auth::check()) {
            Auth::login($person);
            $request->session()->regenerate();
            $request->session()->put('login_art', 'link');
        }

        return view('public.anmeldung-ergebnis', [
            'person' => $person,
            'teilnahme' => $teilnahme,
            'zugeteilt' => $teilnahme->status === TeilnahmeStatus::Zugeteilt,
        ]);
    }

    private function boerse(): ?Boerse
    {
        return Boerse::query()->offen()->where('verkaufstag', '>=', today())->orderBy('verkaufstag')->with('ort')->first();
    }
}
