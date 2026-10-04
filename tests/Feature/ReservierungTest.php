<?php

use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\TeilnahmeStatus;
use App\Models\Nummernreservierung;
use App\Models\Person;

it('gibt bei Absage die reservierte Nummer für diese Börse frei und lässt die Warteliste nachrücken', function () {
    $boerse = neueBoerse(['nummer_von' => 200, 'nummer_bis' => 201, 'kapazitaet' => 2]);
    $stamm = Person::factory()->create();
    $reservierung = Nummernreservierung::create(['person_id' => $stamm->id, 'nummer' => 200]);

    $teilnahme = app(Anmelden::class)($boerse, $stamm, mailSenden: false);
    app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $warte = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    expect($warte->status)->toBe(TeilnahmeStatus::Warteliste);

    app(Absagen::class)($teilnahme);

    expect($warte->fresh()->status)->toBe(TeilnahmeStatus::Angeboten)
        ->and($warte->fresh()->nummer)->toBe(200)
        ->and($reservierung->fresh())->not->toBeNull() // bleibt für spätere Börsen
        ->and(Nummernreservierung::gueltigFuer(neueBoerse())->count())->toBe(1);
});

it('lässt das Orga-Team eine Reservierung nur für diese Börse freigeben und zurücknehmen', function () {
    $boerse = neueBoerse();
    $orga = tap(Person::factory()->create())->assignRole('orga');
    $reservierung = Nummernreservierung::create(['person_id' => Person::factory()->create()->id, 'nummer' => 250]);
    $als = $this->actingAs($orga)->withSession(['login_art' => 'passwort', 'boerse_id' => $boerse->id]);

    $als->post(route('admin.reservierungen.freigeben', $reservierung))->assertSessionHas('erfolg');
    expect(Nummernreservierung::gueltigFuer($boerse)->count())->toBe(0);

    $als->delete(route('admin.reservierungen.freigabe-zuruecknehmen', $reservierung))->assertSessionHas('erfolg');
    expect(Nummernreservierung::gueltigFuer($boerse)->count())->toBe(1);

    $als->get(route('admin.reservierungen.index'))->assertOk()->assertSee('Nur diesmal freigeben');
    $als->delete(route('admin.reservierungen.destroy', $reservierung))->assertSessionHas('erfolg');
    expect(Nummernreservierung::count())->toBe(0);
});
