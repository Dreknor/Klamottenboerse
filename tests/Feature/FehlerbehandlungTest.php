<?php

use App\Domain\Kommunikation\Postausgang;
use App\Enums\NachrichtStatus;
use App\Models\Mailvorlage;
use App\Models\Person;
use App\Support\Fehlermeldung;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Mailer\Exception\TransportException;

it('übersetzt typische Mailserver-Fehler in verständliche Sätze', function () {
    expect(Fehlermeldung::fuer(new TransportException('Failed to authenticate on SMTP server with username "x" using the following authenticators: "LOGIN". Authenticator "LOGIN" returned "Expected response code "235" but got code "535"')))
        ->toContain('Anmeldung abgelehnt')
        ->and(Fehlermeldung::fuer(new TransportException('Connection could not be established with host "ssl://mail.example.org:465": stream_socket_client(): php_network_getaddresses: getaddrinfo failed')))
        ->toContain('nicht erreichbar')
        ->and(Fehlermeldung::fuer(new RuntimeException('irgendwas Unbekanntes')))
        ->toBe(Fehlermeldung::ALLGEMEIN);
});

it('vermerkt einen gescheiterten Mailversand verständlich im Postausgang', function () {
    Mail::shouldReceive('to')->andThrow(new TransportException('Expected response code "235" but got code "535", with message "535 5.7.8 Authentication failed".'));
    $person = Person::factory()->create();

    $nachricht = Postausgang::einplanen($person, 'login_link');
    expect(Postausgang::senden($nachricht))->toBeFalse();

    $nachricht->refresh();
    expect($nachricht->status)->toBe(NachrichtStatus::Fehler)
        ->and($nachricht->fehler)->toContain('Anmeldung abgelehnt')->toContain('Technisch: ');
});

it('zeigt bei unerwarteten Fehlern eine verständliche Meldung statt 500', function () {
    config(['app.debug' => false]);
    Route::middleware('web')->get('/test/kaputt', fn () => throw new RuntimeException('Boom'));
    Route::middleware('web')->post('/test/kaputt', fn () => throw new RuntimeException('Boom'));

    $this->get('/test/kaputt')->assertStatus(500)->assertSee('Das hat leider nicht geklappt')->assertDontSee('Boom');

    $this->from('/formular')->post('/test/kaputt', ['name' => 'Anna', 'password' => 'geheim'])
        ->assertRedirect('/formular')
        ->assertSessionHas('fehler', Fehlermeldung::ALLGEMEIN)
        ->assertSessionHasInput('name', 'Anna')
        ->assertSessionMissing('_old_input.password');
});

it('zeigt deutsche Fehlerseiten ohne technische Texte', function () {
    config(['app.debug' => false]);

    $this->get('/gibt-es-nicht')->assertNotFound()->assertSee('Diese Seite gibt es nicht')->assertDontSee('could not be found');
});

it('ergänzt fehlende Standard-Mailvorlagen bei jedem migrate', function () {
    Mailvorlage::query()->delete();

    Artisan::call('migrate');

    expect(Mailvorlage::query()->where('schluessel', 'nummer_zugeteilt')->exists())->toBeTrue();
});
