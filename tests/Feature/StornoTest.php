<?php

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Models\Person;

function stornoOrga($test)
{
    return $test->actingAs(tap(Person::factory()->create())->assignRole('orga'))->withSession(['login_art' => 'passwort']);
}

it('storniert eine Position, passt Bonsumme und Abrechnung an', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $bon = bon($boerse, [[$v->nummer, 1, 1000], [$v->nummer, 2, 600]]);
    app(AbrechnungBerechnen::class)($boerse);
    $als = stornoOrga($this)->withSession(['boerse_id' => $boerse->id]);

    $als->get(route('admin.verkaeufe.index'))->assertOk()->assertSee('16,00 €');
    $als->post(route('admin.verkaeufe.position', $bon->positionen()->where('artikelnummer', 2)->sole()), ['grund' => 'falsch gescannt'])
        ->assertSessionHas('erfolg');

    expect($bon->fresh()->summe_cent)->toBe(1000)
        ->and($v->abrechnung->fresh()->umsatz_cent)->toBe(1000);
});

it('storniert ganze Bons und nimmt den Storno zurück', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $bon = bon($boerse, [[$v->nummer, 1, 1000]]);
    $als = stornoOrga($this);

    $als->post(route('admin.verkaeufe.bon', $bon), [])->assertSessionHasErrors('grund');
    $als->post(route('admin.verkaeufe.bon', $bon), ['grund' => 'Kunde hat zurückgegeben'])->assertSessionHas('erfolg');
    expect($bon->fresh()->storniert_at)->not->toBeNull();

    $als->delete(route('admin.verkaeufe.zuruecknehmen', $bon))->assertSessionHas('erfolg');
    expect($bon->fresh()->storniert_at)->toBeNull();
});

it('verhindert Stornos nach der Auszahlung', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $bon = bon($boerse, [[$v->nummer, 1, 1000]]);
    app(AbrechnungBerechnen::class)($boerse);
    $v->abrechnung->update(['ausgezahlt_at' => now()]);

    stornoOrga($this)->post(route('admin.verkaeufe.bon', $bon), ['grund' => 'zu spät'])->assertSessionHas('fehler');
    expect($bon->fresh()->storniert_at)->toBeNull();
});

it('speichert Notizen an einer Teilnahme', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $als = stornoOrga($this)->withSession(['boerse_id' => $boerse->id]);

    $als->post(route('admin.teilnahmen.notiz', $v), ['text' => 'bringt drei Kisten'])->assertSessionHas('erfolg');
    $als->get(route('admin.teilnahmen.index'))->assertSee('bringt drei Kisten');
    $als->get(route('admin.personen.show', $v->person_id))->assertSee('bringt drei Kisten');
});
