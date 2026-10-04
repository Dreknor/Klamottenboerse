<?php

use App\Enums\BoerseStatus;
use App\Enums\KinderhausBezug;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Bonposition;
use App\Models\Nummernreservierung;
use App\Models\Person;
use App\Models\Schicht;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Baut die wichtigsten V1-Tabellen in einer zweiten SQLite-Datenbank nach. */
function v1Datenbank(): void
{
    config(['database.connections.v1' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
    DB::purge('v1');
    $s = Schema::connection('v1');

    $s->create('users', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('email');
        $t->string('password');
        $t->boolean('verwaltung')->default(0);
        $t->boolean('kasse')->default(0);
        $t->timestamps();
        $t->softDeletes();
    });
    $s->create('interessenten', function (Blueprint $t) {
        $t->id();
        $t->string('anrede')->nullable();
        $t->string('vorname');
        $t->string('nachname');
        $t->string('mail')->nullable();
        $t->string('telefon')->nullable();
        $t->string('handy')->nullable();
        $t->boolean('mitarbeiter')->nullable();
        $t->boolean('kinderhaus')->nullable();
        $t->unsignedInteger('user_id')->nullable();
        $t->uuid('uuid')->nullable();
        $t->timestamp('email_verified_at')->nullable();
        $t->timestamp('deletion_requested_at')->nullable();
        $t->timestamps();
        $t->softDeletes();
    });
    $s->create('settings', function (Blueprint $t) {
        $t->id();
        $t->string('name');
        $t->string('kinderhaus');
        $t->date('datum');
        $t->integer('provision');
        $t->timestamps();
    });
    $s->create('klamottenboerse', function (Blueprint $t) {
        $t->id();
        $t->string('datum');
        $t->string('anmeldung');
        $t->string('anmeldungKinderhaus');
        $t->time('anlieferung_von');
        $t->time('anlieferung_bis');
        $t->time('abholung_von');
        $t->time('abholung_bis');
        $t->integer('maxTeile');
        $t->string('ort')->nullable();
        $t->string('adresse')->nullable();
        $t->text('belehrung')->nullable();
        $t->boolean('ergebnis_freigabe')->default(false);
        $t->boolean('live_verkaufsansicht_freigabe')->default(false);
        $t->timestamps();
        $t->softDeletes();
    });
    $s->create('vknummern', function (Blueprint $t) {
        $t->id();
        $t->integer('vknummer');
        $t->integer('klamottenboersen_id');
        $t->integer('reserviert_fuer')->nullable();
        $t->integer('vergeben_an')->nullable();
        $t->decimal('umsatz')->nullable();
        $t->timestamps();
        $t->softDeletes();
    });
    $s->create('warteliste', function (Blueprint $t) {
        $t->id();
        $t->integer('interessenten_id');
        $t->timestamps();
    });
    $s->create('verkaeufe', function (Blueprint $t) {
        $t->id();
        $t->integer('user_id');
        $t->integer('klamottenboerse_id')->nullable();
        $t->float('summe');
        $t->timestamps();
    });
    $s->create('verkaufteartikel', function (Blueprint $t) {
        $t->id();
        $t->integer('klamottenboerse_id')->nullable();
        $t->integer('verkauf');
        $t->integer('vknummer');
        $t->integer('artikelnummer');
        $t->float('betrag');
    });
    $s->create('helfer', function (Blueprint $t) {
        $t->id();
        $t->integer('klamottenboerse_id');
        $t->text('name');
        $t->text('telefon');
        $t->text('mail');
        $t->text('bereich');
        $t->timestamps();
    });
    $s->create('appointments', function (Blueprint $t) {
        $t->id();
        $t->integer('klamottenboerse_id');
        $t->string('beschreibung');
        $t->string('bereich')->nullable();
        $t->dateTime('date_start');
        $t->dateTime('date_end');
        $t->integer('helfer_id')->nullable();
        $t->timestamp('erinnerung_versendet_at')->nullable();
        $t->timestamps();
        $t->softDeletes();
    });
    $s->create('notizen', function (Blueprint $t) {
        $t->id();
        $t->integer('interessenten_id');
        $t->text('notiz')->nullable();
        $t->timestamps();
    });
    $s->create('vknummern_kommentar', function (Blueprint $t) {
        $t->id();
        $t->integer('vknummer');
        $t->text('kommentar');
        $t->timestamps();
    });

    $v1 = DB::connection('v1');
    $jetzt = now();
    $v1->table('users')->insert(['id' => 1, 'name' => 'Olga Orga', 'email' => 'olga@example.org', 'password' => bcrypt('altes-passwort'), 'verwaltung' => 1]);
    $v1->table('interessenten')->insert([
        ['id' => 1, 'vorname' => 'Olga', 'nachname' => 'Orga', 'mail' => 'olga@example.org', 'mitarbeiter' => 0, 'kinderhaus' => 1, 'user_id' => 1, 'created_at' => $jetzt, 'deletion_requested_at' => null],
        ['id' => 2, 'vorname' => 'Vera', 'nachname' => 'Verkauf', 'mail' => 'Vera@Example.org', 'mitarbeiter' => 0, 'kinderhaus' => 0, 'user_id' => null, 'created_at' => $jetzt, 'deletion_requested_at' => null],
        ['id' => 3, 'vorname' => 'Vera', 'nachname' => 'Doppelt', 'mail' => 'vera@example.org', 'mitarbeiter' => 0, 'kinderhaus' => 0, 'user_id' => null, 'created_at' => $jetzt, 'deletion_requested_at' => null],
        ['id' => 4, 'vorname' => 'Lea', 'nachname' => 'Löschen', 'mail' => 'lea@example.org', 'mitarbeiter' => 0, 'kinderhaus' => 0, 'user_id' => null, 'created_at' => $jetzt, 'deletion_requested_at' => $jetzt],
    ]);
    $v1->table('settings')->insert(['name' => 'x', 'kinderhaus' => 'y', 'datum' => '2026-03-21', 'provision' => 25]);
    $v1->table('klamottenboerse')->insert([
        ['id' => 1, 'datum' => '2026-03-21', 'anmeldung' => '0000-00-00', 'anmeldungKinderhaus' => '2026-02-10', 'anlieferung_von' => '14:30', 'anlieferung_bis' => '17:30',
            'abholung_von' => '17:00', 'abholung_bis' => '18:30', 'maxTeile' => 60, 'ort' => 'Luthersaal', 'adresse' => 'Altkötzschenbroda 40'],
    ]);
    $v1->table('vknummern')->insert([
        ['id' => 1, 'vknummer' => 201, 'klamottenboersen_id' => 1, 'vergeben_an' => 2, 'reserviert_fuer' => 2],
        ['id' => 2, 'vknummer' => 202, 'klamottenboersen_id' => 1, 'vergeben_an' => 1, 'reserviert_fuer' => null],
        ['id' => 3, 'vknummer' => 202, 'klamottenboersen_id' => 1, 'vergeben_an' => 3, 'reserviert_fuer' => null], // doppelte Nummer
        ['id' => 4, 'vknummer' => 600, 'klamottenboersen_id' => 1, 'vergeben_an' => 1, 'reserviert_fuer' => null],
        ['id' => 5, 'vknummer' => 305, 'klamottenboersen_id' => 1, 'vergeben_an' => null, 'reserviert_fuer' => 1],
    ]);
    $v1->table('verkaeufe')->insert([
        ['id' => 1, 'user_id' => 1, 'klamottenboerse_id' => 1, 'summe' => 12.3, 'created_at' => '2026-03-21 09:10:00'],
        ['id' => 2, 'user_id' => 1, 'klamottenboerse_id' => 1, 'summe' => 4.0, 'created_at' => '2026-03-21 10:00:00'],
    ]);
    $v1->table('verkaufteartikel')->insert([
        ['klamottenboerse_id' => 1, 'verkauf' => 1, 'vknummer' => 201, 'artikelnummer' => 1, 'betrag' => 2.1],
        ['klamottenboerse_id' => 1, 'verkauf' => 1, 'vknummer' => 202, 'artikelnummer' => 4, 'betrag' => 10.2],
        ['klamottenboerse_id' => 1, 'verkauf' => 2, 'vknummer' => 600, 'artikelnummer' => 1, 'betrag' => 4.0],
    ]);
    $v1->table('helfer')->insert(['id' => 1, 'klamottenboerse_id' => 1, 'name' => 'Hans Helfer', 'telefon' => '0351', 'mail' => '', 'bereich' => '']);
    $v1->table('appointments')->insert([
        ['klamottenboerse_id' => 1, 'beschreibung' => 'Kasse', 'bereich' => 'Kasse', 'date_start' => '2026-03-21 08:00', 'date_end' => '2026-03-21 12:00', 'helfer_id' => 1],
        ['klamottenboerse_id' => 1, 'beschreibung' => 'Kasse', 'bereich' => 'Kasse', 'date_start' => '2026-03-21 08:00', 'date_end' => '2026-03-21 12:00', 'helfer_id' => null],
    ]);
    $v1->table('notizen')->insert(['interessenten_id' => 2, 'notiz' => 'Bringt immer zwei Kisten']);
}

it('übernimmt V1-Daten mit stimmenden Prüfsummen', function () {
    v1Datenbank();

    $this->artisan('v1:import')->assertSuccessful();

    // Personen: Dublette per E-Mail zusammengeführt, Löschwunsch nicht übernommen, Helfer als Person
    expect(Person::count())->toBe(3)
        ->and(Person::where('email', 'lea@example.org')->exists())->toBeFalse();
    $olga = Person::where('email', 'olga@example.org')->sole();
    $vera = Person::where('email', 'vera@example.org')->sole();
    expect($olga->hasRole('orga'))->toBeTrue()
        ->and(Hash::check('altes-passwort', $olga->password))->toBeTrue()
        ->and($olga->kinderhaus_bezug)->toBe(KinderhausBezug::Familie)
        ->and($vera->info_mails_erlaubt_at)->not->toBeNull()
        ->and($vera->notizen()->count())->toBe(1);

    // Börse: Null-Datum wird zu null, Termine aus Uhrzeiten
    $boerse = Boerse::sole();
    expect($boerse->status)->toBe(BoerseStatus::Abgeschlossen)
        ->and($boerse->anmeldung_ab)->toBeNull()
        ->and($boerse->anlieferung_beginn->format('d.m. H:i'))->toBe('20.03. 14:30')
        ->and($boerse->provision_promille)->toBe(250);

    // Kinderhaus-Nummer und Verkäufe
    $kinderhaus = $boerse->kinderhausTeilnahme;
    expect($kinderhaus->abrechnung->umsatz_cent)->toBe(400)->and($kinderhaus->abrechnung->spende_cent)->toBe(0);
    expect($boerse->teilnahmen()->where('nummer', 202)->count())->toBe(1);
    expect((int) Bonposition::sum('preis_cent'))->toBe(1630);

    $veraTeilnahme = $boerse->teilnahmen()->where('person_id', $vera->id)->sole();
    expect($veraTeilnahme->status)->toBe(TeilnahmeStatus::Ausgezahlt)
        ->and($veraTeilnahme->abrechnung->spende_cent)->toBe(53); // 25 % von 2,10 €

    // Reservierungen der letzten Börse werden dauerhaft
    expect(Nummernreservierung::query()->whereNull('boerse_id')->pluck('nummer')->sort()->values()->all())->toBe([201, 305]);

    // Schichten: gleiche Zeiten → eine Schicht mit Soll 2, Helfer ohne Mail als Person
    $schicht = Schicht::sole();
    expect($schicht->soll)->toBe(2)->and($schicht->zusagen()->sole()->person->name)->toBe('Hans Helfer');
});

it('bricht ab, wenn V2 schon Daten enthält', function () {
    v1Datenbank();
    Person::factory()->create();

    $this->artisan('v1:import')->assertFailed();
});

it('speichert im Probelauf nichts', function () {
    v1Datenbank();

    $this->artisan('v1:import --probe')->assertSuccessful();

    expect(Person::count())->toBe(0)->and(Boerse::count())->toBe(0);
});
