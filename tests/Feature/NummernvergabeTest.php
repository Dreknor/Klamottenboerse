<?php

use App\Domain\Boersen\Actions\BoerseAnlegen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Domain\Teilnahme\Nummernvergabe;
use App\Enums\TeilnahmeStatus;
use App\Models\Boerse;
use App\Models\Nummernreservierung;
use App\Models\Person;
use App\Models\Teilnahme;
use Database\Factories\BoerseFactory;

function neueBoerse(array $werte = []): Boerse
{
    return app(BoerseAnlegen::class)((new BoerseFactory)->anmeldungOffen()->raw($werte));
}

function vergangeneTeilnahme(Person $person, int $nummer): void
{
    $alt = neueBoerse(['verkaufstag' => now()->subYear()->toDateString()]);
    Teilnahme::create([
        'boerse_id' => $alt->id, 'person_id' => $person->id, 'nummer' => $nummer,
        'status' => TeilnahmeStatus::Ausgezahlt,
    ]);
}

it('legt die Kinderhaus-Nummer 600 automatisch und spendenfrei an', function () {
    $boerse = neueBoerse();

    $kinderhaus = $boerse->kinderhausTeilnahme;
    expect($kinderhaus->nummer)->toBe(600)
        ->and($kinderhaus->spendenfrei)->toBeTrue()
        ->and($kinderhaus->status)->toBe(TeilnahmeStatus::Zugeteilt)
        ->and($boerse->belegteNummern())->toBe(0); // zählt nicht zur Kapazität
});

it('vergibt die Kinderhaus-Nummer niemals an Verkäufer', function () {
    $boerse = neueBoerse(['nummer_von' => 598, 'nummer_bis' => 600, 'blockgroesse' => 100, 'kapazitaet' => 10]);

    $nummern = collect(range(1, 3))->map(fn () => app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false)->nummer);

    expect($nummern->all())->toBe([598, 599, null]);
});

it('bietet die zuletzt genutzte Nummer wieder an', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();
    vergangeneTeilnahme($person, 437);

    $teilnahme = app(Anmelden::class)($boerse, $person, mailSenden: false);

    expect($teilnahme->nummer)->toBe(437);
});

it('gibt die letzte Nummer an den, der zuerst kommt – auch ohne frühere Teilnahme', function () {
    $boerse = neueBoerse();
    $stamm = Person::factory()->create();
    vergangeneTeilnahme($stamm, 200);

    // Neue Verkäuferin kommt zuerst und erhält die niedrigste freie Nummer im schwächsten Block: 200.
    $neu = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $spaeter = app(Anmelden::class)($boerse, $stamm, mailSenden: false);

    expect($neu->nummer)->toBe(200)
        ->and($spaeter->nummer)->not->toBe(200)
        ->and($spaeter->status)->toBe(TeilnahmeStatus::Zugeteilt);
});

it('verteilt neue Nummern gleichmäßig auf die 100er-Blöcke', function () {
    $boerse = neueBoerse(['kapazitaet' => 40]);

    foreach (range(1, 40) as $_) {
        app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    }

    expect((new Nummernvergabe($boerse))->blockbelegung())->toBe([200 => 10, 300 => 10, 400 => 10, 500 => 10]);
});

it('gibt die letzte Nummer nicht, wenn ihr Block über dem Zielwert liegt', function () {
    $boerse = neueBoerse(['kapazitaet' => 8, 'block_toleranz' => 0]); // Ziel: 2 je Block
    foreach ([200, 201] as $nummer) {
        Teilnahme::create(['boerse_id' => $boerse->id, 'person_id' => Person::factory()->create()->id, 'nummer' => $nummer, 'status' => TeilnahmeStatus::Zugeteilt]);
    }
    $person = Person::factory()->create();
    vergangeneTeilnahme($person, 250);

    $teilnahme = app(Anmelden::class)($boerse, $person, mailSenden: false);

    expect($teilnahme->nummer)->toBe(300);
});

it('hält reservierte Nummern frei und gibt sie der reservierten Person', function () {
    $boerse = neueBoerse(['nummer_von' => 200, 'nummer_bis' => 202, 'kapazitaet' => 3]);
    $reserviert = Person::factory()->create();
    Nummernreservierung::create(['person_id' => $reserviert->id, 'nummer' => 200]);

    $a = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $b = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $c = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $r = app(Anmelden::class)($boerse, $reserviert, mailSenden: false);

    expect([$a->nummer, $b->nummer])->toBe([201, 202])
        ->and($c->status)->toBe(TeilnahmeStatus::Warteliste) // Platz der Reservierung bleibt frei
        ->and($r->nummer)->toBe(200);
});

it('setzt Anmeldungen bei voller Kapazität auf die Warteliste', function () {
    $boerse = neueBoerse(['kapazitaet' => 2]);

    $teilnahmen = collect(range(1, 4))->map(fn () => app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false));

    expect($teilnahmen->pluck('status')->all())->toBe([
        TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Zugeteilt, TeilnahmeStatus::Warteliste, TeilnahmeStatus::Warteliste,
    ])->and($teilnahmen->pluck('wartelisten_position')->filter()->values()->all())->toBe([1, 2]);
});

it('meldet dieselbe Person nicht doppelt an', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create();

    $erste = app(Anmelden::class)($boerse, $person, mailSenden: false);
    $zweite = app(Anmelden::class)($boerse, $person, mailSenden: false);

    expect($zweite->id)->toBe($erste->id)
        ->and($boerse->teilnahmen()->where('person_id', $person->id)->count())->toBe(1);
});
