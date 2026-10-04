<?php

use App\Models\Person;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

it('schickt Team-Mitgliedern einen Link, Verkäufern nicht', function () {
    Notification::fake();
    $team = tap(Person::factory()->create())->assignRole('kasse');
    $verkaeufer = Person::factory()->create();

    $this->post(route('password.email'), ['email' => $team->email])->assertSessionHas('erfolg');
    $this->post(route('password.email'), ['email' => $verkaeufer->email])->assertSessionHas('erfolg');

    Notification::assertSentTo($team, ResetPassword::class);
    Notification::assertNotSentTo($verkaeufer, ResetPassword::class);
});

it('setzt das Passwort mit gültigem Link neu', function () {
    $team = tap(Person::factory()->create(['password' => 'altes-passwort-1']))->assignRole('orga');
    $token = Password::createToken($team);

    $this->get(route('password.reset', ['token' => $token, 'email' => $team->email]))->assertOk();
    $this->post(route('password.update'), [
        'token' => $token, 'email' => $team->email, 'password' => 'neues-passwort-2', 'password_confirmation' => 'neues-passwort-2',
    ])->assertRedirect(route('login'));

    expect(Hash::check('neues-passwort-2', $team->fresh()->password))->toBeTrue();
    $this->post(route('login'), ['email' => $team->email, 'password' => 'neues-passwort-2'])->assertRedirect(route('admin.dashboard'));
});

it('lässt das eigene Passwort im Konto ändern', function () {
    $orga = tap(Person::factory()->create(['password' => 'altes-passwort-1']))->assignRole('orga');
    $als = $this->actingAs($orga)->withSession(['login_art' => 'passwort']);

    $als->get(route('admin.konto.edit'))->assertOk();
    $als->put(route('admin.konto.passwort'), ['aktuelles_passwort' => 'falsch', 'password' => 'neues-passwort-2', 'password_confirmation' => 'neues-passwort-2'])
        ->assertSessionHasErrors('aktuelles_passwort');
    $als->put(route('admin.konto.passwort'), ['aktuelles_passwort' => 'altes-passwort-1', 'password' => 'neues-passwort-2', 'password_confirmation' => 'neues-passwort-2'])
        ->assertSessionHas('erfolg');

    expect(Hash::check('neues-passwort-2', $orga->fresh()->password))->toBeTrue();
});
