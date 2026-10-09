<?php

use App\Models\Person;
use App\Models\Seite;
use App\Support\Einstellungen;

it('zeigt Impressum und Datenschutz mit Betreiber-Angaben aus den Einstellungen', function () {
    Einstellungen::set('betreiber_name', 'Förderverein Ev. Kinderhaus e. V.');
    Einstellungen::set('kontakt_email', 'info@example.org');

    $this->get(route('impressum'))->assertOk()
        ->assertSee('Förderverein Ev. Kinderhaus e. V.')
        ->assertSee('info@example.org')
        ->assertSee('[bitte ergänzen: Anschrift]', false);

    $this->get(route('datenschutz'))->assertOk()
        ->assertSee('nur technisch notwendige Cookies', false)
        ->assertSee('Förderverein Ev. Kinderhaus e. V.');
});

it('zeigt auf öffentlichen Seiten den Cookie-Hinweis und die Pflicht-Links', function () {
    $this->get(route('start'))->assertOk()
        ->assertSee('Cookies auf dieser Website')
        ->assertSee(route('impressum'))
        ->assertSee(route('datenschutz'))
        ->assertSee('images/logo-schriftzug.png?v=');
});

it('lässt das Orga-Team die Seiten bearbeiten', function () {
    $orga = tap(Person::factory()->create())->assignRole('orga');
    $seite = Seite::where('slug', 'impressum')->sole();

    $this->actingAs($orga)->withSession(['login_art' => 'passwort'])
        ->put(route('admin.seiten.update', $seite), ['titel' => 'Impressum', 'inhalt' => "## Neu\n\nGeänderter Text"])
        ->assertSessionHas('erfolg');

    $this->get(route('impressum'))->assertSee('Geänderter Text');
    expect($seite->fresh()->bearbeitet_von)->toBe($orga->id);
});

it('speichert Telefonnummern mit führender Null unverändert', function () {
    $admin = tap(Person::factory()->create())->assignRole('admin');

    $this->actingAs($admin)->withSession(['login_art' => 'passwort'])->put(route('admin.einstellungen.update'), [
        'vereinsname' => 'Klamottenbörse', 'empfaenger_spende' => 'Förderverein',
        'mail_max_pro_stunde' => 55, 'erinnerung_aufgaben_tage' => 2, 'kontakt_telefon' => '0351 1234567',
    ])->assertSessionHas('erfolg');

    expect(Einstellungen::get('kontakt_telefon'))->toBe('0351 1234567')
        ->and(Einstellungen::get('mail_max_pro_stunde'))->toBe(55);
});
