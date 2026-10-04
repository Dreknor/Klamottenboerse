<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KinderhausBezug;
use App\Http\Controllers\Controller;
use App\Models\Person;
use Database\Seeders\GrunddatenSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

/** Team & Rechte: wer darf ins Backend, an die Kasse, ans Tablet. Nur für Admins. */
class TeamController extends Controller
{
    public function index(): View
    {
        return view('admin.team.index', [
            'team' => Person::query()->whereHas('roles')->with('roles')->orderBy('nachname')->orderBy('vorname')->get(),
            'rollen' => GrunddatenSeeder::ROLLEN,
        ]);
    }

    /** Bekannte Person ins Team holen oder neue Person anlegen – auf Wunsch mit Einladungslink. */
    public function store(Request $request): RedirectResponse
    {
        $daten = $request->validate([
            'person_id' => ['nullable', 'required_without:nachname', 'exists:personen,id'],
            'vorname' => ['nullable', 'required_without:person_id', 'string', 'max:100'],
            'nachname' => ['nullable', 'required_without:person_id', 'string', 'max:100'],
            'email' => ['nullable', 'required_without:person_id', 'email', 'max:190', Rule::unique('personen', 'email')],
            'rollen' => ['required', 'array', 'min:1'],
            'rollen.*' => [Rule::in(array_keys(GrunddatenSeeder::ROLLEN))],
            'einladen' => ['nullable', 'boolean'],
        ], [
            'rollen.required' => 'Bitte mindestens eine Rolle wählen.',
            'person_id.required_without' => 'Bitte eine bekannte Person wählen oder eine neue anlegen.',
        ]);

        $person = $daten['person_id'] ?? null
            ? Person::findOrFail($daten['person_id'])
            : Person::create([
                'vorname' => $daten['vorname'], 'nachname' => $daten['nachname'], 'email' => $daten['email'],
                'kinderhaus_bezug' => KinderhausBezug::Keiner,
            ]);

        $person->syncRoles(array_unique([...$person->getRoleNames()->all(), ...$daten['rollen']]));
        activity()->performedOn($person)->log('ins Team aufgenommen: '.implode(', ', $daten['rollen']));

        $hinweis = '';
        if ($request->boolean('einladen')) {
            $hinweis = $this->linkSenden($person) ? ' Eine Einladung zum Passwort-Setzen ist unterwegs.' : ' Ohne E-Mail-Adresse konnte keine Einladung verschickt werden.';
        }

        return back()->with('erfolg', "{$person->name} ist jetzt im Team.".$hinweis);
    }

    public function update(Request $request, Person $person): RedirectResponse
    {
        $rollen = $request->validate([
            'rollen' => ['array'],
            'rollen.*' => [Rule::in(array_keys(GrunddatenSeeder::ROLLEN))],
        ])['rollen'] ?? [];

        if ($fehler = $this->letzterAdmin($request, $person, $rollen)) {
            return back()->with('fehler', $fehler);
        }

        $person->syncRoles($rollen);
        activity()->performedOn($person)->log('Rollen geändert: '.($rollen ? implode(', ', $rollen) : 'keine'));

        return back()->with('erfolg', "Rechte von {$person->name} gespeichert.");
    }

    public function destroy(Request $request, Person $person): RedirectResponse
    {
        if ($fehler = $this->letzterAdmin($request, $person, [])) {
            return back()->with('fehler', $fehler);
        }

        $person->syncRoles([]);
        $person->forceFill(['password' => null])->save();
        activity()->performedOn($person)->log('aus dem Team entfernt');

        return back()->with('erfolg', "{$person->name} hat keinen Team-Zugang mehr. Die Person bleibt in der Kartei.");
    }

    public function passwortLink(Person $person): RedirectResponse
    {
        return $this->linkSenden($person)
            ? back()->with('erfolg', "Link zum Passwort-Setzen an {$person->email} verschickt.")
            : back()->with('fehler', 'Diese Person hat keine E-Mail-Adresse.');
    }

    private function linkSenden(Person $person): bool
    {
        if (! $person->email) {
            return false;
        }

        return Password::sendResetLink(['email' => $person->email]) === Password::RESET_LINK_SENT;
    }

    /** Es muss immer mindestens ein Admin bleiben – und niemand sperrt sich selbst aus. */
    private function letzterAdmin(Request $request, Person $person, array $neueRollen): ?string
    {
        if (! $person->hasRole('admin') || in_array('admin', $neueRollen, true)) {
            return null;
        }
        if ($person->is($request->user())) {
            return 'Du kannst dir die Admin-Rechte nicht selbst entziehen.';
        }
        if (Role::findByName('admin')->users()->count() <= 1) {
            return 'Es muss mindestens ein Admin bleiben.';
        }

        return null;
    }
}
