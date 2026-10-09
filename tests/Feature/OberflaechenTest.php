<?php

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\TeilnahmeStatus;
use App\Models\Bon;
use App\Models\Mailvorlage;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Schicht;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

function orga(): Person
{
    return tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole('orga');
}

function alsOrga($test, ?Person $person = null)
{
    return $test->actingAs($person ?? orga())->withSession(['login_art' => 'passwort']);
}

it('zeigt alle Backend-Seiten ohne Fehler', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();
    app(Anmelden::class)($boerse, $person, mailSenden: false);
    Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Kasse', 'beginn' => now(), 'ende' => now()->addHour()]);

    $seiten = ['admin.dashboard', 'admin.boersen.index', 'admin.boersen.create', 'admin.teilnahmen.index', 'admin.reservierungen.index',
        'admin.personen.index', 'admin.personen.create', 'admin.schichten.index', 'admin.aufgaben.index', 'admin.checklistenvorlagen.index',
        'admin.kalender.index', 'admin.mailvorlagen.index', 'admin.mailvorlagen.create', 'admin.mailplan.index', 'admin.postausgang.index', 'admin.posteingang.index',
        'admin.abrechnung.index', 'admin.abrechnung.auszahlungsplan', 'admin.statistik.index', 'admin.feedback.index'];

    $admin = tap(orga())->assignRole('admin');
    foreach ($seiten as $seite) {
        alsOrga($this, $admin)->get(route($seite))->assertOk();
    }
    alsOrga($this, $admin)->get(route('admin.personen.show', $person))->assertOk()->assertSee($person->nachname);
    alsOrga($this, $admin)->get(route('admin.boersen.edit', $boerse))->assertOk();
    alsOrga($this, $admin)->get(route('admin.einstellungen.edit'))->assertOk();
});

it('lässt per Magic-Link angemeldete Personen nicht ins Backend', function () {
    $orga = orga();

    $this->get(URL::temporarySignedRoute('portal.login', now()->addHour(), ['person' => $orga->uuid]))->assertRedirect(route('portal.index'));
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    $this->get(route('kasse.index'))->assertRedirect(route('login'));
});

it('verweigert Verkäufern das Backend', function () {
    $this->actingAs(Person::factory()->create())->withSession(['login_art' => 'passwort'])
        ->get(route('admin.dashboard'))->assertForbidden();
});

it('meldet online an: Bestätigungsmail, dann Nummer', function () {
    Mail::fake();
    $boerse = neueBoerse();

    $this->post(route('anmeldung.store'), [
        'vorname' => 'Lena', 'nachname' => 'Neu', 'email' => 'lena@example.org',
        'kinderhaus_bezug' => 'keiner', 'datenschutz' => '1', 'info_mails' => '1',
    ])->assertOk()->assertSee('Fast geschafft');

    $mail = Nachricht::query()->where('typ', 'anmeldung_bestaetigen')->sole();
    preg_match('/\((http[^)]+bestaetigen[^)]+)\)/', $mail->inhalt, $treffer);
    expect($treffer)->not->toBeEmpty();

    $this->get(html_entity_decode($treffer[1]))->assertOk()->assertSee('Verkäufernummer');

    $teilnahme = $boerse->teilnahmen()->whereHas('person', fn ($q) => $q->where('email', 'lena@example.org'))->sole();
    expect($teilnahme->status)->toBe(TeilnahmeStatus::Zugeteilt)
        ->and($teilnahme->person->info_mails_erlaubt_at)->not->toBeNull();
});

it('lässt Kinderhaus-Familien vor dem allgemeinen Start zu, andere nicht', function () {
    $boerse = neueBoerse(['anmeldung_kinderhaus_ab' => now()->subDay(), 'anmeldung_ab' => now()->addDay()]);
    $daten = ['vorname' => 'A', 'nachname' => 'B', 'datenschutz' => '1'];

    $this->post(route('anmeldung.store'), $daten + ['email' => 'familie@example.org', 'kinderhaus_bezug' => 'familie'])->assertOk();
    $this->post(route('anmeldung.store'), $daten + ['email' => 'andere@example.org', 'kinderhaus_bezug' => 'keiner'])
        ->assertRedirect()->assertSessionHas('fehler');
});

it('nimmt Bons der Kasse an – doppelt gesendet nur einmal gespeichert', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $kasse = tap(Person::factory()->create())->assignRole('kasse');
    $bon = ['uuid' => (string) Str::uuid(), 'erstellt_am' => now()->toIso8601String(), 'kasse' => 'Kasse 1',
        'positionen' => [['nummer' => $v->nummer, 'artikel' => 3, 'preis_cent' => 450]]];

    $this->actingAs($kasse)->withSession(['login_art' => 'passwort'])->getJson(route('kasse.daten'))
        ->assertOk()->assertJsonFragment(['boerse_id' => $boerse->id]);
    $this->postJson(route('kasse.sync'), $bon)->assertCreated();
    $this->postJson(route('kasse.sync'), $bon)->assertCreated();
    $this->postJson(route('kasse.sync'), ['uuid' => (string) Str::uuid(), 'positionen' => [['nummer' => 999, 'artikel' => 1, 'preis_cent' => 100]]] + $bon)
        ->assertStatus(422);

    expect(Bon::count())->toBe(1)->and(Bon::sole()->kassenschicht->kasse->name)->toBe('Kasse 1');
});

it('erfasst Artikel im Portal und erzeugt Etiketten und Kistenzettel als PDF', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();
    $teilnahme = app(Anmelden::class)($boerse, $person, mailSenden: false);

    $this->actingAs($person)->post(route('portal.artikel.store'), ['beschreibung' => 'Matschhose', 'groesse' => '104', 'preis' => '5,50', 'anzahl' => 2])
        ->assertRedirect();

    expect($teilnahme->artikel()->pluck('laufnummer')->all())->toBe([1, 2])
        ->and($teilnahme->artikel()->first()->etikettCode())->toBe(sprintf('%03d00100550', $teilnahme->nummer));

    $this->get(route('portal.index'))->assertOk()->assertSee('Matschhose');
    $this->get(route('portal.etiketten'))->assertOk()->assertHeader('content-type', 'application/pdf');
    $this->get(route('portal.kistenzettel'))->assertOk()->assertHeader('content-type', 'application/pdf');
});

it('nimmt Kisten an und gibt Erlös am Tablet aus', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $helfer = tap(Person::factory()->create())->assignRole('annahme');
    $tablet = $this->actingAs($helfer)->withSession(['login_art' => 'passwort']);

    $tablet->get(route('tablet.annahme', ['suche' => $v->nummer]))->assertOk()->assertSee((string) $v->nummer);
    $tablet->post(route('tablet.annehmen', $v), ['kisten' => 2])->assertRedirect();
    expect($v->fresh()->status)->toBe(TeilnahmeStatus::Angeliefert)->and($v->kisten()->count())->toBe(2);

    bon($boerse, [[$v->nummer, 1, 1000]]);
    app(AbrechnungBerechnen::class)($boerse);

    $tablet->get(route('tablet.ausgabe', ['suche' => $v->nummer]))->assertOk()->assertSee('7,50 €');
    $tablet->get(route('tablet.rueckpacken', ['nummer' => $v->nummer]))->assertOk();
    $tablet->post(route('tablet.auszahlen', $v))->assertRedirect();

    expect($v->fresh()->status)->toBe(TeilnahmeStatus::Ausgezahlt)
        ->and($v->kisten()->whereNull('ausgegeben_at')->count())->toBe(0);
});

it('trägt Helfer öffentlich ein und lässt sie per Link absagen', function () {
    $boerse = neueBoerse();
    $schicht = Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Café', 'beginn' => $boerse->verkaufstag->copy()->setTime(9, 0),
        'ende' => $boerse->verkaufstag->copy()->setTime(12, 0), 'soll' => 1]);

    $this->post(route('helfer.eintragen', $schicht), ['vorname' => 'Hanna', 'nachname' => 'Hilft', 'email' => 'hanna@example.org'])
        ->assertRedirect(route('helfer.index'));
    $this->post(route('helfer.eintragen', $schicht), ['vorname' => 'Zu', 'nachname' => 'Spät', 'email' => 'spaet@example.org'])
        ->assertSessionHas('fehler');

    $einteilung = $schicht->einteilungen()->sole();
    $this->post(URL::signedRoute('helfer.absage', ['einteilung' => $einteilung->id]))->assertOk();
    expect($einteilung->fresh()->status->value)->toBe('abgesagt');
});

it('erfasst Helfer manuell im Backend – auch ohne E-Mail', function () {
    $boerse = neueBoerse();
    $schicht = Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Aufbau', 'beginn' => now(), 'ende' => now()->addHour()]);

    alsOrga($this)->post(route('admin.schichten.helfer', $schicht), ['vorname' => 'Opa', 'nachname' => 'Otto', 'telefon' => '0351 123'])
        ->assertSessionHas('erfolg');

    expect($schicht->zusagen()->sole()->person->email)->toBeNull();
});

it('speichert Feedback über den persönlichen Link', function () {
    $boerse = neueBoerse();
    $feedback = $boerse->feedback()->create(['rolle' => 'verkaeufer', 'token' => Str::random(40)]);

    $this->get(route('feedback.show', $feedback->token))->assertOk();
    $this->post(route('feedback.store', $feedback->token), ['bewertung' => 5, 'gut' => 'Alles prima'])->assertOk();

    expect($feedback->fresh()->bewertung)->toBe(5)->and($feedback->fresh()->beantwortet_at)->not->toBeNull();
});

it('kann Info-Mails per Link abbestellen', function () {
    $person = Person::factory()->create();

    $this->get(URL::signedRoute('infomails.abbestellen', ['person' => $person->uuid]))->assertOk();

    expect($person->fresh()->info_mails_erlaubt_at)->toBeNull();
});

it('liefert der Kasse einen frischen Sicherheits-Token und den Service Worker', function () {
    $kasse = tap(Person::factory()->create())->assignRole('kasse');

    $this->actingAs($kasse)->withSession(['login_art' => 'passwort'])
        ->getJson(route('kasse.token'))->assertOk()->assertJsonStructure(['token']);
    expect(file_get_contents(public_path('kasse-sw.js')))->toContain("const SEITE = '/kasse'");
});

it('legt eigene Mailvorlagen an und löscht nur diese', function () {
    alsOrga($this)->post(route('admin.mailvorlagen.store'), ['name' => 'Dank an Helfer', 'betreff' => 'Danke, {vorname}!', 'inhalt' => 'Hallo {vorname}, danke!'])
        ->assertRedirect();
    alsOrga($this)->post(route('admin.mailvorlagen.store'), ['name' => 'Dank an Helfer', 'betreff' => 'Nochmal', 'inhalt' => 'Text'])
        ->assertRedirect();

    $eigene = Mailvorlage::query()->where('schluessel', 'eigen_dank_an_helfer')->firstOrFail();
    expect(Mailvorlage::query()->where('schluessel', 'eigen_dank_an_helfer_2')->exists())->toBeTrue();
    alsOrga($this)->get(route('admin.mailvorlagen.edit', $eigene))->assertOk()->assertSee('Löschen');

    $standard = Mailvorlage::query()->where('schluessel', 'nummer_zugeteilt')->firstOrFail();
    alsOrga($this)->delete(route('admin.mailvorlagen.destroy', $standard));
    alsOrga($this)->delete(route('admin.mailvorlagen.destroy', $eigene))->assertRedirect(route('admin.mailvorlagen.index'));

    expect(Mailvorlage::query()->whereKey($standard->id)->exists())->toBeTrue()
        ->and(Mailvorlage::query()->whereKey($eigene->id)->exists())->toBeFalse();
});

it('benachrichtigt das Orga-Team, wenn ein Helfer absagt – auch bei doppeltem Klick nur einmal', function () {
    $boerse = neueBoerse();
    $orga = admin();
    $schicht = Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Kasse', 'beginn' => $boerse->verkaufstag->copy()->setTime(9, 0),
        'ende' => $boerse->verkaufstag->copy()->setTime(11, 0), 'soll' => 2]);
    $helfer = Person::factory()->create(['vorname' => 'Hanna', 'nachname' => 'Hilft']);
    $einteilung = $schicht->einteilungen()->create(['person_id' => $helfer->id]);

    $link = URL::signedRoute('helfer.absage', ['einteilung' => $einteilung->id]);
    $this->post($link)->assertOk();
    $this->post($link)->assertOk();

    $mail = Nachricht::query()->where('typ', 'helfer_abgesagt')->sole();
    expect($mail->person_id)->toBe($orga->id)
        ->and($mail->betreff)->toContain('Hanna Hilft')->toContain('Kasse')
        ->and($mail->inhalt)->toContain('0 von 2');
});
