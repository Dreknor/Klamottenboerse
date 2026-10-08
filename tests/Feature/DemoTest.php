<?php

use App\Domain\Kommunikation\Postausgang;
use App\Enums\NachrichtStatus;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Posteingang;
use App\Support\Demo;
use Database\Seeders\DemoSeeder;

function demoAn(): void
{
    config(['demo.aktiv' => true]);
}

it('erzeugt vollständige Beispieldaten mit Zugängen für jede Rolle', function () {
    $this->seed(DemoSeeder::class);

    foreach (Demo::ZUGAENGE as [$email]) {
        expect(Person::where('email', $email)->exists())->toBeTrue();
    }
    expect(Person::where('email', 'demo-kasse@example.org')->sole()->hasRole('kasse'))->toBeTrue()
        ->and(Posteingang::count())->toBe(3)
        // keine echte Adresse unter den Beispielpersonen
        ->and(Person::whereNotNull('email')->pluck('email')->reject(fn ($e) => Demo::istBeispieladresse($e)))->toBeEmpty();
});

it('meldet in der Demo per Klick in einer Rolle an', function () {
    demoAn();
    $kasse = tap(Person::factory()->create(['email' => 'demo-kasse@example.org']))->assignRole('kasse');
    Person::factory()->create(['email' => 'demo-verkaeufer@example.org']);

    $this->get(route('login'))->assertSee('Demo: als … ausprobieren')->assertSee('noindex', false);
    $this->post(route('demo.anmelden', 'kasse'))->assertRedirect(route('kasse.index'));
    $this->assertAuthenticatedAs($kasse);
    expect(session('login_art'))->toBe('passwort');

    $this->post(route('demo.anmelden', 'verkaeufer'))->assertRedirect(route('portal.index'));
    expect(session('login_art'))->toBe('link');
});

it('bietet die Klick-Anmeldung ohne Demo-Modus nicht an', function () {
    Person::factory()->create(['email' => 'demo-admin@example.org']);

    $this->post(route('demo.anmelden', 'admin'))->assertNotFound();
    $this->get(route('login'))->assertDontSee('Demo: als');
});

it('schickt Demo-Mails nur an echte Adressen und markiert sie mit [DEMO]', function () {
    demoAn();
    config(['mail.default' => 'array']);
    $beispiel = Nachricht::create(['email' => 'jemand@example.org', 'typ' => 'test', 'betreff' => 'Hallo', 'inhalt' => 'x', 'status' => NachrichtStatus::Wartend]);
    $echt = Nachricht::create(['email' => 'tester@gmx.de', 'typ' => 'test', 'betreff' => 'Hallo', 'inhalt' => 'x', 'status' => NachrichtStatus::Wartend]);

    Postausgang::senden($beispiel);
    Postausgang::senden($echt);

    $gesendet = app('mailer')->getSymfonyTransport()->messages();
    expect($gesendet)->toHaveCount(1)
        ->and($gesendet->first()->getOriginalMessage()->getSubject())->toBe('[DEMO] Hallo')
        ->and($beispiel->fresh()->status)->toBe(NachrichtStatus::Versendet)
        ->and($beispiel->fresh()->versendet_at)->toBeNull(); // zählt nicht aufs Stundenlimit
});

it('sperrt Updates in der Demo und das Zurücksetzen außerhalb der Demo', function () {
    demoAn();
    $admin = tap(Person::factory()->create())->assignRole('admin');
    $this->actingAs($admin)->withSession(['login_art' => 'passwort'])
        ->post(route('admin.system.update'), ['bestaetigung' => '1'])->assertSessionHas('fehler');

    config(['demo.aktiv' => false]);
    $this->artisan('demo:zuruecksetzen')->assertExitCode(1);
    expect(fn () => Demo::zuruecksetzen())->toThrow(RuntimeException::class);
});

it('erkennt Beispieladressen', function (string $email, bool $beispiel) {
    expect(Demo::istBeispieladresse($email))->toBe($beispiel);
})->with([
    ['a@example.org', true], ['b@example.com', true], ['c@klamottenboerse.test', true], ['d@sub.example', true],
    ['e@gmx.de', false], ['f@examples.org', false], ['g@kinderhaus-radebeul.de', false],
]);
