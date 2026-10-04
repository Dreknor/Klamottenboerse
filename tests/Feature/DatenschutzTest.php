<?php

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Personen\InaktiveBereinigen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\TeilnahmeStatus;
use App\Models\Abrechnung;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Teilnahme;
use Illuminate\Support\Facades\URL;

function alteTeilnahme(Person $person, int $monateHer): void
{
    $boerse = neueBoerse(['verkaufstag' => now()->subMonths($monateHer)->toDateString()]);
    Teilnahme::create(['boerse_id' => $boerse->id, 'person_id' => $person->id, 'nummer' => 300, 'status' => TeilnahmeStatus::Ausgezahlt]);
}

it('schreibt Inaktive an und löscht sie nach der Frist – Aktive und Team bleiben', function () {
    $inaktiv = Person::factory()->create(['created_at' => now()->subYears(4)]);
    alteTeilnahme($inaktiv, 30);
    $aktiv = Person::factory()->create(['created_at' => now()->subYears(4)]);
    alteTeilnahme($aktiv, 6);
    $team = tap(Person::factory()->create(['created_at' => now()->subYears(4)]))->assignRole('orga');
    $meldetSich = Person::factory()->create(['created_at' => now()->subYears(4)]);

    $ergebnis = app(InaktiveBereinigen::class)();
    expect($ergebnis['angeschrieben'])->toBe(2)
        ->and(Nachricht::query()->where('typ', 'inaktiv_loeschung')->pluck('person_id')->sort()->values()->all())
        ->toBe(collect([$inaktiv->id, $meldetSich->id])->sort()->values()->all());

    // Eine Person klickt auf den Link im Portal
    $this->travel(10)->days();
    $this->get(URL::temporarySignedRoute('portal.login', now()->addDay(), ['person' => $meldetSich->uuid]));

    $this->travel(20)->days();
    expect(app(InaktiveBereinigen::class)()['geloescht'])->toBe(1)
        ->and(Person::find($inaktiv->id))->toBeNull()
        ->and(Person::find($meldetSich->id))->not->toBeNull()
        ->and(Person::find($aktiv->id))->not->toBeNull()
        ->and(Person::find($team->id))->not->toBeNull()
        ->and(Teilnahme::query()->whereNull('person_id')->where('nummer', 300)->exists())->toBeTrue();
});

it('exportiert die eigenen Daten als Datei', function () {
    $person = Person::factory()->create(['vorname' => 'Ida']);
    app(Anmelden::class)(neueBoerse(), $person, mailSenden: false);

    $antwort = $this->actingAs($person)->get(route('portal.daten.export'))->assertOk();

    expect($antwort->headers->get('content-disposition'))->toContain('meine-daten')
        ->and($antwort->json('person.vorname'))->toBe('Ida')
        ->and($antwort->json('teilnahmen'))->toHaveCount(1);
});

it('löscht auf Wunsch sofort – oder nach der Abrechnung bei laufender Teilnahme', function () {
    $ohne = Person::factory()->create();
    $this->actingAs($ohne)->post(route('portal.daten.loeschen'), ['bestaetigung' => '1'])->assertRedirect(route('start'));
    expect(Person::find($ohne->id))->toBeNull();

    $boerse = neueBoerse();
    $mit = Person::factory()->create();
    $teilnahme = app(Anmelden::class)($boerse, $mit, mailSenden: false);
    $this->actingAs($mit)->post(route('portal.daten.loeschen'), ['bestaetigung' => '1'])->assertSessionHas('erfolg');
    expect($mit->fresh()->loeschung_angefragt_at)->not->toBeNull();

    bon($boerse, [[$teilnahme->nummer, 1, 500]]);
    app(AbrechnungBerechnen::class)($boerse);
    $teilnahme->abrechnung->update(['ausgezahlt_at' => now()]);
    $teilnahme->update(['status' => TeilnahmeStatus::Ausgezahlt]);

    app(InaktiveBereinigen::class)();
    expect(Person::find($mit->id))->toBeNull()
        ->and(Abrechnung::query()->where('teilnahme_id', $teilnahme->id)->value('umsatz_cent'))->toBe(500);
});

it('lässt das Orga-Team eine Person mit Namensbestätigung löschen', function () {
    $orga = tap(Person::factory()->create())->assignRole('orga');
    $person = Person::factory()->create(['nachname' => 'Beispiel']);
    $als = $this->actingAs($orga)->withSession(['login_art' => 'passwort']);

    $als->delete(route('admin.personen.destroy', $person), ['bestaetigung' => 'falsch'])->assertSessionHasErrors('bestaetigung');
    $als->delete(route('admin.personen.destroy', $person), ['bestaetigung' => 'Beispiel'])->assertRedirect(route('admin.personen.index'));
    $als->get(route('admin.personen.inaktive'))->assertOk();

    expect(Person::withTrashed()->find($person->id))->toBeNull();
});
