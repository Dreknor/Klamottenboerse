<?php

use App\Models\Person;

dataset('teamseiten', ['admin.aufgaben.index', 'admin.kalender.index', 'admin.protokolle.index', 'admin.ablage.index', 'admin.statistik.index', 'admin.feedback.index', 'admin.konto.edit']);

it('lässt alle Team-Mitglieder in Aufgaben, Kalender, Protokolle, Ablage und Auswertung', function (string $route) {
    neueBoerse();
    foreach (['kasse', 'annahme'] as $rolle) {
        $person = tap(Person::factory()->create())->assignRole($rolle);
        $this->actingAs($person)->withSession(['login_art' => 'passwort'])->get(route($route))->assertOk();
    }
})->with('teamseiten');

it('hält Kasse- und Annahme-Rollen aus dem übrigen Orga-Backend heraus', function () {
    neueBoerse();
    $kasse = tap(Person::factory()->create())->assignRole('kasse');
    $als = $this->actingAs($kasse)->withSession(['login_art' => 'passwort']);

    $als->get(route('admin.dashboard'))->assertForbidden();
    $als->get(route('admin.personen.index'))->assertForbidden();
    $als->get(route('admin.aufgaben.index'))->assertOk()
        ->assertSee('Protokolle &amp; Ablage', false)
        ->assertDontSee('Nachricht schreiben')
        ->assertDontSee('Checklisten-Vorlage bearbeiten');
});

it('verlangt für den Team-Bereich eine Rolle und den Passwort-Login', function () {
    neueBoerse();
    $ohneRolle = Person::factory()->create();
    $this->actingAs($ohneRolle)->withSession(['login_art' => 'passwort'])->get(route('admin.aufgaben.index'))->assertForbidden();

    $kasse = tap(Person::factory()->create())->assignRole('kasse');
    $this->actingAs($kasse)->withSession(['login_art' => 'link'])->get(route('admin.aufgaben.index'))->assertRedirect(route('login'));
});
