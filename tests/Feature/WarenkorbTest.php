<?php

use App\Domain\Teilnahme\Actions\Anmelden;
use App\Models\Bon;
use App\Models\Person;
use App\Models\WarenkorbPosition;
use Illuminate\Support\Str;

function kassierer(): Person
{
    return tap(Person::factory()->create())->assignRole('kasse');
}

it('teilt den Warenkorb zwischen Handy und PC desselben Kontos', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $kasse = kassierer();

    // Handy scannt
    $this->actingAs($kasse)->withSession(['login_art' => 'passwort'])
        ->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => (string) Str::uuid(), 'nummer' => $v->nummer, 'artikel' => 3, 'preis_cent' => 450])
        ->assertCreated()->assertJsonPath('summe_cent', 450);

    // PC (andere Sitzung, gleiches Konto) sieht den Artikel und kassiert ab
    $this->flushSession();
    $pc = $this->actingAs($kasse)->withSession(['login_art' => 'passwort']);
    $pc->getJson(route('kasse.warenkorb'))->assertOk()->assertJsonCount(1, 'positionen');
    $pc->postJson(route('kasse.warenkorb.abschliessen'), ['bon_uuid' => (string) Str::uuid(), 'kasse' => 'PC'])
        ->assertCreated()->assertJsonPath('bon.summe_cent', 450)->assertJsonCount(0, 'positionen');

    expect(Bon::sole()->positionen()->sole()->artikelnummer)->toBe(3)
        ->and(WarenkorbPosition::count())->toBe(0);
    $pc->getJson(route('kasse.warenkorb'))->assertJsonPath('letzter_bon.summe_cent', 450);
});

it('hält die Warenkörbe verschiedener Konten getrennt', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $a = kassierer();
    $b = kassierer();

    $this->actingAs($a)->withSession(['login_art' => 'passwort'])
        ->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => (string) Str::uuid(), 'nummer' => $v->nummer, 'artikel' => 1, 'preis_cent' => 100]);

    $this->actingAs($b)->withSession(['login_art' => 'passwort'])->getJson(route('kasse.warenkorb'))->assertJsonCount(0, 'positionen');
});

it('lehnt unbekannte Nummern und doppelte Artikel ab und warnt bei Preisabweichung', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $v->artikel()->create(['laufnummer' => 5, 'beschreibung' => 'Jacke', 'preis_cent' => 800]);
    $als = $this->actingAs(kassierer())->withSession(['login_art' => 'passwort']);

    $als->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => (string) Str::uuid(), 'nummer' => 999, 'artikel' => 1, 'preis_cent' => 100])
        ->assertStatus(422)->assertJsonPath('message', 'Verkäufernummer 999 ist nicht vergeben. Etikett prüfen!');
    $als->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => (string) Str::uuid(), 'nummer' => $v->nummer, 'artikel' => 5, 'preis_cent' => 700])
        ->assertCreated()->assertJsonPath('warnungen.0', 'Preis weicht vom erfassten Preis ab (8,00 €).');
    $als->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => (string) Str::uuid(), 'nummer' => $v->nummer, 'artikel' => 5, 'preis_cent' => 700])
        ->assertStatus(422);
});

it('entfernt offline verkaufte Artikel aus dem Warenkorb, sobald der Bon übertragen ist', function () {
    $boerse = neueBoerse(['verkaufstag' => today()->toDateString()]);
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $als = $this->actingAs(kassierer())->withSession(['login_art' => 'passwort']);
    $uuid = (string) Str::uuid();

    $als->postJson(route('kasse.warenkorb.hinzufuegen'), ['uuid' => $uuid, 'nummer' => $v->nummer, 'artikel' => 2, 'preis_cent' => 300]);
    $als->postJson(route('kasse.sync'), ['uuid' => (string) Str::uuid(), 'erstellt_am' => now()->toIso8601String(),
        'positionen' => [['uuid' => $uuid, 'nummer' => $v->nummer, 'artikel' => 2, 'preis_cent' => 300]]])->assertCreated();

    expect(WarenkorbPosition::count())->toBe(0)->and(Bon::count())->toBe(1);
});
