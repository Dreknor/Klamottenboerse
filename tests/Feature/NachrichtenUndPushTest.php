<?php

use App\Domain\Kommunikation\Postausgang;
use App\Domain\Push\Push;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\PushAbo;
use App\Models\PushNachricht;

function pushAbo(Person $person): PushAbo
{
    return PushAbo::create([
        'person_id' => $person->id, 'endpoint' => 'https://push.example.org/'.$person->id,
        'endpoint_hash' => hash('sha256', 'https://push.example.org/'.$person->id), 'p256dh' => 'x', 'auth' => 'y',
    ]);
}

it('meldet ein Gerät für Push an und wieder ab', function () {
    $person = Person::factory()->create();
    $abo = ['endpoint' => 'https://fcm.googleapis.com/fcm/send/abc', 'keys' => ['p256dh' => 'BKey', 'auth' => 'AKey']];

    $this->actingAs($person)->postJson(route('push.speichern'), $abo)->assertOk();
    $this->actingAs($person)->postJson(route('push.speichern'), $abo)->assertOk(); // doppelt = ein Gerät
    expect(PushAbo::where('person_id', $person->id)->count())->toBe(1);

    $this->actingAs($person)->deleteJson(route('push.loeschen'), ['endpoint' => $abo['endpoint']])->assertOk();
    expect(PushAbo::count())->toBe(0);
});

it('erzeugt die Push-Schlüssel automatisch', function () {
    expect(Push::oeffentlicherSchluessel())->toBeString()->not->toBeEmpty()
        ->and(Push::oeffentlicherSchluessel())->toBe(Push::oeffentlicherSchluessel());
});

it('schickt Erinnerungen zusätzlich als Push, aber nur an Personen mit Gerät', function () {
    $boerse = neueBoerse();
    $mitPush = Person::factory()->create();
    $ohnePush = Person::factory()->create();
    pushAbo($mitPush);

    Postausgang::einplanen($mitPush, 'erinnerung_helfer', $boerse);
    Postausgang::einplanen($ohnePush, 'erinnerung_helfer', $boerse);
    Postausgang::einplanen($mitPush, 'absage_bestaetigt', $boerse); // Vorlage ohne Push

    expect(Nachricht::count())->toBe(3)
        ->and(PushNachricht::sole()->person_id)->toBe($mitPush->id)
        ->and(PushNachricht::sole()->text)->not->toContain('](');
});

it('schreibt Gruppen und einzelne Personen an', function () {
    $boerse = neueBoerse();
    $verkaeufer = Person::factory()->count(2)->create();
    foreach ($verkaeufer as $p) {
        app(Anmelden::class)($boerse, $p, mailSenden: false);
    }
    $team = tap(Person::factory()->create())->assignRole('orga');
    pushAbo($team);
    $einzeln = Person::factory()->create();
    $ich = tap(Person::factory()->create(['email' => null]))->assignRole('admin');

    $als = $this->actingAs($ich)->withSession(['login_art' => 'passwort', 'boerse_id' => $boerse->id]);
    $als->postJson(route('admin.rundnachricht.vorschau'), ['gruppen' => ['verkaeufer', 'team'], 'personen' => [$einzeln->id]])
        ->assertOk()->assertJsonPath('gesamt', 4)->assertJsonPath('push', 1);

    $als->post(route('admin.rundnachricht.store'), [
        'gruppen' => ['verkaeufer', 'team'], 'personen' => [$einzeln->id],
        'betreff' => 'Info für {vorname}', 'text' => "Hallo {vorname},\nbitte beachten …", 'mail' => '1', 'push' => '1',
    ])->assertRedirect(route('admin.postausgang.index'));

    expect(Nachricht::where('typ', 'rundnachricht')->count())->toBe(4)
        ->and(Nachricht::where('person_id', $einzeln->id)->sole()->betreff)->toBe('Info für '.$einzeln->vorname)
        ->and(PushNachricht::sole()->person_id)->toBe($team->id);
});

it('verlangt mindestens einen Versandweg', function () {
    $ich = tap(Person::factory()->create())->assignRole('orga');

    $this->actingAs($ich)->withSession(['login_art' => 'passwort'])
        ->post(route('admin.rundnachricht.store'), ['gruppen' => ['team'], 'betreff' => 'x', 'text' => 'y'])
        ->assertSessionHas('fehler');
    expect(Nachricht::count())->toBe(0);
});
