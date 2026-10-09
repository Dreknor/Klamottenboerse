<?php

use App\Domain\Kasse\BonErfassen;
use App\Domain\Statistik\BoersenStatistik;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Models\Kategorie;
use App\Models\Person;
use Illuminate\Support\Str;

it('verteilt den Umsatz auf Kategorien und Größen', function () {
    $boerse = neueBoerse();
    $v = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $kleidung = Kategorie::query()->where('name', 'Kleidung Gr. 74–92')->sole();
    $spielzeug = Kategorie::query()->where('name', 'Spielzeug & Spiele')->sole();

    foreach ([
        [1, $kleidung, 'Gr. 86 / 92', 400],
        [2, $kleidung, '86/92', 600],
        [3, $kleidung, '74', 300],
        [4, $spielzeug, null, 1000],
        [5, $spielzeug, null, 500],
    ] as [$nr, $kategorie, $groesse, $preis]) {
        $v->artikel()->create(['laufnummer' => $nr, 'beschreibung' => 'Test', 'kategorie_id' => $kategorie->id, 'groesse' => $groesse, 'preis_cent' => $preis]);
    }

    app(BonErfassen::class)($boerse, null, [
        'uuid' => (string) Str::uuid(), 'erstellt_am' => now()->toIso8601String(),
        'positionen' => [
            ['nummer' => $v->nummer, 'artikel' => 1, 'preis_cent' => 400],
            ['nummer' => $v->nummer, 'artikel' => 2, 'preis_cent' => 600],
            ['nummer' => $v->nummer, 'artikel' => 3, 'preis_cent' => 300],
            ['nummer' => $v->nummer, 'artikel' => 4, 'preis_cent' => 1000],
            ['nummer' => $v->nummer, 'artikel' => 99, 'preis_cent' => 250],
        ],
    ]);

    $s = BoersenStatistik::fuer($boerse);

    expect(collect($s['nach_kategorie'])->keyBy('name')->map(fn ($k) => [$k['umsatz'], $k['verkauft'], $k['erfasst'], $k['quote']])->all())
        ->toBe([
            'Kleidung Gr. 74–92' => [1300, 3, 3, 100.0],
            'Spielzeug & Spiele' => [1000, 1, 2, 50.0],
            'Ohne erfasstes Etikett' => [250, 1, 0, null],
        ])
        ->and($s['nach_groesse'])->toBe([
            '74' => ['umsatz' => 300, 'verkauft' => 1],
            '86/92' => ['umsatz' => 1000, 'verkauft' => 2],
        ]);

    alsAdmin($this)->get(route('admin.statistik.index'))->assertOk()
        ->assertSee('Umsatz nach Kategorie')->assertSee('86/92')->assertDontSee('100er-Block');
});
