<?php

use App\Models\Kategorie;
use App\Models\Person;
use Illuminate\Support\Facades\Mail;

function teamAdmin($test)
{
    return $test->actingAs(tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole(['orga', 'admin']))
        ->withSession(['login_art' => 'passwort']);
}

it('hat die Kategorien aus V1 als Standard', function () {
    expect(Kategorie::query()->sortiert()->pluck('name')->first())->toBe('Kleidung Gr. 50–68')
        ->and(Kategorie::fuerGroesse('86/92')?->name)->toBe('Kleidung Gr. 74–92')
        ->and(Kategorie::fuerGroesse('XL'))->toBeNull();
});

it('fragt bei der Anmeldung ab, was Verkäufer mitbringen', function () {
    Mail::fake();
    neueBoerse();
    $spielzeug = Kategorie::query()->where('name', 'Spielzeug & Spiele')->sole();
    $kleidung = Kategorie::query()->where('name', 'Kleidung Gr. 74–92')->sole();
    Kategorie::query()->where('name', 'Umstandsmode')->update(['aktiv' => false]);

    $this->get(route('anmeldung.create'))->assertOk()->assertSee('Was bringst du überwiegend mit?')
        ->assertSee('Spielzeug &amp; Spiele', false)->assertDontSee('Umstandsmode');

    $this->post(route('anmeldung.store'), [
        'vorname' => 'Lena', 'nachname' => 'Neu', 'email' => 'lena@example.org', 'kinderhaus_bezug' => 'keiner',
        'datenschutz' => '1', 'kategorien' => [$spielzeug->id, $kleidung->id],
    ])->assertOk();

    expect(Person::query()->where('email', 'lena@example.org')->sole()->kategorien->pluck('id')->sort()->values()->all())
        ->toBe(collect([$spielzeug->id, $kleidung->id])->sort()->values()->all());
});

it('lässt das Orga-Team Kategorien pflegen und bei Personen erfassen', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();

    teamAdmin($this)->get(route('admin.kategorien.index'))->assertOk()->assertSee('Angebot: '.$boerse->titel, false);
    teamAdmin($this)->post(route('admin.kategorien.store'), ['name' => 'Faschingskostüme', 'gruppe' => 'Weiteres'])->assertSessionHas('erfolg');
    teamAdmin($this)->post(route('admin.kategorien.store'), ['name' => 'Falsch', 'gruppe' => 'Kleidung', 'groesse_von' => 100, 'groesse_bis' => 50])
        ->assertSessionHasErrors('groesse_bis');

    $kostueme = Kategorie::query()->where('name', 'Faschingskostüme')->sole();
    teamAdmin($this)->get(route('admin.personen.edit', $person))->assertOk()->assertSee('Faschingskostüme');
    teamAdmin($this)->put(route('admin.personen.update', $person), [
        'vorname' => $person->vorname, 'nachname' => $person->nachname, 'email' => $person->email,
        'kinderhaus_bezug' => 'keiner', 'kategorien_gesendet' => '1', 'kategorien' => [$kostueme->id],
    ])->assertRedirect();

    expect($person->kategorien()->pluck('name')->all())->toBe(['Faschingskostüme']);
    teamAdmin($this)->get(route('admin.personen.show', $person))->assertSee('Faschingskostüme');
});

it('benennt Gruppen um, legt sie zusammen und ändert ihre Reihenfolge', function () {
    teamAdmin($this)->get(route('admin.kategorien.index'))->assertOk()->assertSee('Gruppen speichern');

    teamAdmin($this)->put(route('admin.kategorien.gruppen'), ['gruppen' => [
        ['alt' => 'Kleidung', 'name' => 'Kinderkleidung', 'position' => 2],
        ['alt' => 'Weiteres', 'name' => 'Alles andere', 'position' => 1],
    ]])->assertSessionHas('erfolg');

    expect(Kategorie::query()->sortiert()->pluck('gruppe')->unique()->values()->all())->toBe(['Alles andere', 'Kinderkleidung'])
        ->and(Kategorie::query()->sortiert()->first()->name)->toBe('Schuhe')
        ->and(Kategorie::auswahl()->keys()->all())->toBe(['Alles andere', 'Kinderkleidung']);

    // Gleicher Name = zusammenlegen
    teamAdmin($this)->put(route('admin.kategorien.gruppen'), ['gruppen' => [
        ['alt' => 'Alles andere', 'name' => 'Angebot', 'position' => 1],
        ['alt' => 'Kinderkleidung', 'name' => 'Angebot', 'position' => 2],
    ]]);
    expect(Kategorie::query()->distinct()->pluck('gruppe')->all())->toBe(['Angebot']);
});
