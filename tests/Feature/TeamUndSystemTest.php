<?php

use App\Domain\Kommunikation\VorlagenMail;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\NachrichtStatus;
use App\Models\Fehler;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\SystemUpdate;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;

it('findet Personen live nach Name und nach Nummer der aktuellen Börse', function () {
    $boerse = neueBoerse();
    $mia = Person::factory()->create(['vorname' => 'Mia', 'nachname' => 'Sonnenschein']);
    $t = app(Anmelden::class)($boerse, $mia, mailSenden: false);
    $als = alsAdmin($this)->withSession(['login_art' => 'passwort', 'boerse_id' => $boerse->id]);

    $als->getJson(route('admin.personen.suche', ['q' => 'sonnen']))
        ->assertOk()->assertJsonPath('0.name', 'Sonnenschein, Mia')->assertJsonPath('0.nummer', $t->nummer);
    $als->getJson(route('admin.personen.suche', ['q' => (string) $t->nummer]))->assertJsonCount(1)->assertJsonPath('0.id', $mia->id);
    $als->getJson(route('admin.personen.suche', ['q' => 'xyz-niemand']))->assertJsonCount(0);
});

it('zeigt alle Personen ohne Seitenumbruch', function () {
    Person::factory()->count(60)->create();

    alsAdmin($this)->get(route('admin.personen.index'))->assertOk()->assertSee('61 Personen');
});

it('nimmt eine neue Person ins Team auf und lädt sie ein', function () {
    Notification::fake();

    alsAdmin($this)->post(route('admin.team.store'), [
        'vorname' => 'Tina', 'nachname' => 'Team', 'email' => 'tina@example.org', 'rollen' => ['kasse'], 'einladen' => '1',
    ])->assertRedirect()->assertSessionHas('erfolg');

    $tina = Person::query()->where('email', 'tina@example.org')->sole();
    expect($tina->hasRole('kasse'))->toBeTrue();
    Notification::assertSentTo($tina, ResetPassword::class);
});

it('ändert Rollen, lässt aber den letzten Admin nicht fallen', function () {
    $ich = admin();
    $kollege = tap(Person::factory()->create())->assignRole('orga');

    alsAdmin($this, $ich)->put(route('admin.team.update', $kollege), ['rollen' => ['orga', 'kasse']]);
    expect($kollege->fresh()->getRoleNames()->sort()->values()->all())->toBe(['kasse', 'orga']);

    alsAdmin($this, $ich)->put(route('admin.team.update', $ich), ['rollen' => ['orga']])->assertSessionHas('fehler');
    expect($ich->fresh()->hasRole('admin'))->toBeTrue();

    alsAdmin($this, $ich)->delete(route('admin.team.destroy', $kollege));
    expect($kollege->fresh()->roles)->toBeEmpty()->and($kollege->fresh()->password)->toBeNull();
});

it('lässt Orga-Mitglieder nicht in Team-Verwaltung und System', function () {
    $orga = tap(Person::factory()->create())->assignRole('orga');

    $this->actingAs($orga)->withSession(['login_art' => 'passwort'])->get(route('admin.team.index'))->assertForbidden();
    $this->actingAs($orga)->withSession(['login_art' => 'passwort'])->get(route('admin.system.index'))->assertForbidden();
});

it('schreibt Fehler ins Fehlerprotokoll und fasst Wiederholungen zusammen', function () {
    foreach ([17, 18] as $bon) { // gleiche Stelle im Code, nur andere Daten
        Log::channel('datenbank')->error("Kaputt bei Bon {$bon}", ['exception' => new RuntimeException("Bon {$bon} fehlt")]);
    }
    Log::channel('datenbank')->info('nur eine Info');

    $fehler = Fehler::sole();
    expect($fehler->anzahl)->toBe(2)
        ->and($fehler->klasse)->toBe(RuntimeException::class)
        ->and($fehler->trace)->toContain('Bon 17 fehlt');

    $als = alsAdmin($this);
    $als->get(route('admin.fehler.index'))->assertOk()->assertSee('Kaputt bei Bon 17');
    $als->get(route('admin.fehler.show', $fehler))->assertOk()->assertSee('RuntimeException');
    $als->post(route('admin.fehler.erledigt', $fehler));
    expect($fehler->fresh()->erledigt_at)->not->toBeNull();
});

it('zeigt den Systemzustand', function () {
    Process::fake(['*' => Process::result('abc1234|2026-10-05T10:00:00+02:00|Letzte Änderung')]);

    alsAdmin($this)->get(route('admin.system.index'))->assertOk()->assertSee('Automatische Abläufe')->assertSee('Datenbank');
});

it('installiert ein Update mit git pull und migrate', function () {
    Process::fake([
        '*status --porcelain*' => Process::result(''),
        '*rev-parse HEAD*' => Process::sequence()->push(Process::result('aaa111'))->push(Process::result('bbb222')),
        '*diff --name-only*' => Process::result("app/Foo.php\n"),
        '*' => Process::result('ok'),
    ]);

    alsAdmin($this)->post(route('admin.system.update'), ['bestaetigung' => '1'])->assertSessionHas('erfolg');

    $update = SystemUpdate::sole();
    expect($update->status)->toBe('erfolgreich')
        ->and($update->ausgabe)->toContain('git pull')->toContain('migrate')->toContain('composer install übersprungen');
    Process::assertRan(fn ($p) => in_array('pull', (array) $p->command, true));
    expect(app()->isDownForMaintenance())->toBeFalse();
});

it('bricht das Update ab, wenn auf dem Server Dateien geändert wurden', function () {
    Process::fake([
        '*status --porcelain*' => Process::result(' M app/Foo.php'),
        '*' => Process::result('aaa111'),
    ]);

    alsAdmin($this)->post(route('admin.system.update'), ['bestaetigung' => '1'])->assertSessionHas('fehler');

    expect(SystemUpdate::sole()->status)->toBe('fehlgeschlagen');
    Process::assertDidntRun(fn ($p) => in_array('pull', (array) $p->command, true));
});

it('verschickt eine Testmail und meldet das Ergebnis', function () {
    alsAdmin($this)->get(route('admin.system.index'))->assertOk()->assertSee('Testmail senden');

    // In den Tests ist MAIL_MAILER=array – das verschickt nichts und wird erklärt
    alsAdmin($this)->post(route('admin.system.testmail'), ['email' => 'test@example.de'])
        ->assertSessionHas('fehler', fn ($text) => str_contains($text, 'MAIL_MAILER=array'));

    config(['mail.default' => 'smtp']);
    Mail::fake();
    alsAdmin($this)->post(route('admin.system.testmail'), ['email' => 'test@example.de'])
        ->assertSessionHas('erfolg', fn ($text) => str_contains($text, 'angenommen'));
    Mail::assertSent(VorlagenMail::class, fn ($mail) => $mail->hasTo('test@example.de'));
    expect(Nachricht::query()->where('typ', 'testmail')->value('status'))->toBe(NachrichtStatus::Versendet);
});
