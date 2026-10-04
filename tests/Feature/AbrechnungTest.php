<?php

use App\Domain\Abrechnung\AbrechnungBerechnen;
use App\Domain\Abrechnung\Auszahlungsplan;
use App\Domain\Kasse\BonErfassen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\TeilnahmeStatus;
use App\Models\Bon;
use App\Models\Person;
use Illuminate\Support\Str;

function bon($boerse, array $positionen): Bon
{
    return app(BonErfassen::class)($boerse, null, [
        'uuid' => (string) Str::uuid(),
        'erstellt_am' => now()->toIso8601String(),
        'positionen' => array_map(fn ($p) => ['nummer' => $p[0], 'artikel' => $p[1], 'preis_cent' => $p[2]], $positionen),
    ]);
}

it('rechnet Spende und Auszahlung mit Rundung auf 10 Cent', function () {
    $boerse = neueBoerse();

    // 12,34 € Umsatz: 25 % = 3,085 → 3,09 €; Rest 9,25 € → abgerundet 9,20 €; Spende inkl. Rundung 3,14 €
    expect(AbrechnungBerechnen::rechnen(1234, $boerse, false))
        ->toBe(['umsatz' => 1234, 'spende' => 314, 'auszahlung' => 920]);
});

it('rechnet die Kinderhaus-Nummer ohne Spende ab', function () {
    $boerse = neueBoerse();
    $verkaeufer = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);

    bon($boerse, [[600, 1, 1000], [$verkaeufer->nummer, 1, 1000]]);
    app(AbrechnungBerechnen::class)($boerse);

    $kinderhaus = $boerse->kinderhausTeilnahme->abrechnung;
    expect($kinderhaus->spende_cent)->toBe(0)
        ->and($kinderhaus->auszahlung_cent)->toBe(1000)
        ->and($verkaeufer->fresh()->abrechnung->spende_cent)->toBe(250)
        ->and($verkaeufer->fresh()->status)->toBe(TeilnahmeStatus::Abgerechnet);
});

it('ignoriert stornierte Bons und Positionen', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);

    bon($boerse, [[$v->nummer, 1, 500]]);
    bon($boerse, [[$v->nummer, 2, 700]])->update(['storniert_at' => now()]);
    bon($boerse, [[$v->nummer, 3, 900]])->positionen()->first()->update(['storniert_at' => now()]);
    app(AbrechnungBerechnen::class)($boerse);

    expect($v->fresh()->abrechnung->umsatz_cent)->toBe(500)
        ->and($v->fresh()->abrechnung->verkaufte_artikel)->toBe(1);
});

it('speichert einen Bon nur einmal, auch wenn die Kasse ihn doppelt sendet', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $daten = ['uuid' => (string) Str::uuid(), 'erstellt_am' => now()->toIso8601String(),
        'positionen' => [['nummer' => $v->nummer, 'artikel' => 1, 'preis_cent' => 300]]];

    app(BonErfassen::class)($boerse, null, $daten);
    app(BonErfassen::class)($boerse, null, $daten);

    expect(Bon::count())->toBe(1);
});

it('lehnt Bons mit unbekannter Verkäufernummer ab', function () {
    bon(neueBoerse(), [[999, 1, 300]]);
})->throws(DomainException::class);

it('summiert die Stückelung über alle Auszahlungen', function () {
    $boerse = neueBoerse();
    $a = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $b = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    bon($boerse, [[$a->nummer, 1, 2000], [$b->nummer, 1, 1000], [600, 1, 5000]]);
    app(AbrechnungBerechnen::class)($boerse);

    $plan = new Auszahlungsplan($boerse);

    // 15,00 € = 10 + 5; 7,50 € = 5 + 2 + 0,50 – Kinderhaus wird nicht ausgezahlt
    expect($plan->gesamtCent)->toBe(2250)
        ->and($plan->summe)->toBe([1000 => 1, 500 => 2, 200 => 1, 50 => 1])
        ->and($plan->zeilen)->toHaveCount(2);
});
