<?php

use App\Domain\Reputation\Reputation;
use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
use App\Enums\TeilnahmeStatus;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Vermerk;
use App\Models\VermerkArt;

function vermerkeFuer(Person $person, int $punkte): void
{
    $person->vermerke()->create(['art' => 'Kiste(n) nicht gebracht', 'punkte' => $punkte, 'quelle' => 'backend']);
}

function alsRolle($test, string $rolle)
{
    return $test->actingAs(tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole($rolle))
        ->withSession(['login_art' => 'passwort']);
}

it('berechnet die Reputation aus den Vermerken der letzten Monate', function () {
    $person = Person::factory()->create();
    expect(Reputation::status($person))->toBe(Reputation::GUT);

    vermerkeFuer($person, 2);
    expect(Reputation::status($person))->toBe(Reputation::WARNUNG);

    vermerkeFuer($person, 3);
    expect(Reputation::status($person))->toBe(Reputation::GESPERRT);

    // Alte Vermerke zählen nicht mehr
    Vermerk::query()->update(['created_at' => now()->subMonths(25)]);
    expect(Reputation::status($person))->toBe(Reputation::GUT);

    // Festlegung bei der Person hat Vorrang
    $person->update(['nummernvergabe' => 'haendisch']);
    expect(Reputation::automatischGesperrt($person))->toBeTrue();
});

it('vergibt bei schlechter Reputation keine automatische Nummer, sondern legt eine Anfrage an', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();
    vermerkeFuer($person, 5);

    $teilnahme = app(Anmelden::class)($boerse, $person);

    expect($teilnahme->status)->toBe(TeilnahmeStatus::Angefragt)
        ->and($teilnahme->nummer)->toBeNull()
        ->and(Nachricht::query()->where('typ', 'nummer_angefragt')->where('person_id', $person->id)->exists())->toBeTrue();

    // Meldet das Orga-Team selbst an, ist das eine bewusste Entscheidung
    $andere = Person::factory()->create();
    vermerkeFuer($andere, 5);
    expect(app(Anmelden::class)($boerse, $andere, 'orga', mailSenden: false)->status)->toBe(TeilnahmeStatus::Zugeteilt);
});

it('lässt das Orga-Team über Anfragen entscheiden', function () {
    $boerse = neueBoerse();
    [$eins, $zwei] = Person::factory()->count(2)->create()->each(fn ($p) => vermerkeFuer($p, 5))->all();
    $anfrageEins = app(Anmelden::class)($boerse, $eins, mailSenden: false);
    $anfrageZwei = app(Anmelden::class)($boerse, $zwei, mailSenden: false);

    alsRolle($this, 'orga')->get(route('admin.teilnahmen.index'))->assertSee('2 Nummern-Anfrage(n) warten');

    alsRolle($this, 'orga')->post(route('admin.teilnahmen.anfrage.vergeben', $anfrageEins))->assertSessionHas('erfolg');
    alsRolle($this, 'orga')->post(route('admin.teilnahmen.anfrage.ablehnen', $anfrageZwei))->assertSessionHas('erfolg');

    expect($anfrageEins->fresh()->status)->toBe(TeilnahmeStatus::Zugeteilt)
        ->and($anfrageEins->fresh()->nummer)->not->toBeNull()
        ->and($anfrageZwei->fresh()->status)->toBe(TeilnahmeStatus::Abgesagt)
        ->and(Nachricht::query()->where('typ', 'anfrage_abgelehnt')->where('person_id', $zwei->id)->exists())->toBeTrue();
});

it('lässt Verkäufer mit schlechter Reputation nicht automatisch von der Warteliste nachrücken', function () {
    $boerse = neueBoerse(['kapazitaet' => 1]);
    $erste = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $gesperrt = Person::factory()->create();
    $wartendGesperrt = app(Anmelden::class)($boerse, $gesperrt, mailSenden: false); // noch ohne Vermerke → Warteliste
    $wartendGut = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    expect($wartendGesperrt->status)->toBe(TeilnahmeStatus::Warteliste);

    vermerkeFuer($gesperrt, 5);
    app(Absagen::class)($erste);
    app(WartelisteNachruecken::class)($boerse);

    expect($wartendGesperrt->fresh()->status)->toBe(TeilnahmeStatus::Angefragt)
        ->and($wartendGut->fresh()->status)->toBe(TeilnahmeStatus::Angeboten);
});

it('erfasst Vermerke schnell an Kasse und Annahme über die Verkäufernummer', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create(['vorname' => 'Kai', 'nachname' => 'Kiste']);
    $teilnahme = app(Anmelden::class)($boerse, $person, mailSenden: false);
    $art = VermerkArt::query()->where('name', 'Kiste(n) nicht gebracht')->sole();

    alsRolle($this, 'annahme')->get(route('tablet.annahme', ['suche' => $teilnahme->nummer]))->assertOk()->assertSee('⚑ Vermerk');
    alsRolle($this, 'kasse')->get(route('vermerk.schnell', ['quelle' => 'kasse', 'nummer' => $teilnahme->nummer]))
        ->assertOk()->assertSee('Kiste(n) nicht gebracht');

    alsRolle($this, 'kasse')->post(route('vermerk.speichern'), ['nummer' => 9999, 'vermerk_art_id' => $art->id, 'quelle' => 'kasse'])
        ->assertSessionHasErrors('nummer');
    alsRolle($this, 'kasse')->post(route('vermerk.speichern'), [
        'nummer' => $teilnahme->nummer, 'vermerk_art_id' => $art->id, 'quelle' => 'kasse', 'bemerkung' => '2 von 3 fehlen',
    ])->assertRedirect()->assertSessionHas('erfolg', fn ($t) => str_contains($t, 'Kai K.'));

    $vermerk = $person->vermerke()->sole();
    expect($vermerk->punkte)->toBe(3)->and($vermerk->quelle)->toBe('kasse')->and($vermerk->boerse_id)->toBe($boerse->id);

    // Helfer ohne Orga-Rolle sehen die Übersicht nicht
    alsRolle($this, 'kasse')->get(route('admin.vermerke.index'))->assertForbidden();
});

it('zeigt Reputation im Backend und erlaubt eine Festlegung je Person', function () {
    $person = Person::factory()->create();
    vermerkeFuer($person, 5);

    alsRolle($this, 'orga')->get(route('admin.vermerke.index'))->assertOk()->assertSee($person->name);
    alsRolle($this, 'orga')->get(route('admin.personen.show', $person))->assertOk()->assertSee('Nummer nur händisch');

    alsRolle($this, 'orga')->put(route('admin.personen.nummernvergabe', $person), ['nummernvergabe' => 'frei'])->assertSessionHas('erfolg');
    expect(Reputation::automatischGesperrt($person->fresh()))->toBeFalse();

    alsRolle($this, 'orga')->put(route('admin.reputation.schwellen'), ['reputation_warnung_ab' => 3, 'reputation_sperre_ab' => 2, 'reputation_zeitraum_monate' => 12])
        ->assertSessionHasErrors('reputation_sperre_ab');
});
