<?php

use App\Domain\Kommunikation\Platzhalter;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Models\Person;
use App\Support\Belehrung;

function orgaMitBoerse($test, $boerse)
{
    return $test->actingAs(tap(Person::factory()->create())->assignRole('orga'))
        ->withSession(['login_art' => 'passwort', 'boerse_id' => $boerse->id]);
}

it('erzeugt Verkäuferliste, Belehrungen, Abstreichliste und Helferliste als PDF', function () {
    $boerse = neueBoerse();
    foreach (Person::factory()->count(3)->create() as $p) {
        app(Anmelden::class)($boerse, $p, mailSenden: false);
    }
    $als = orgaMitBoerse($this, $boerse);

    $als->get(route('admin.listen.index'))->assertOk()->assertSee('3 Verkäufer mit Nummer');
    foreach (['verkaeuferliste', 'belehrungen', 'abstreichliste', 'helferliste'] as $liste) {
        $als->get(route('admin.listen.'.$liste))->assertOk()->assertHeader('content-type', 'application/pdf');
    }
});

it('druckt die Belehrung für eine einzelne Nummer und meldet unbekannte Nummern', function () {
    $boerse = neueBoerse();
    $t = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $als = orgaMitBoerse($this, $boerse);

    $als->get(route('admin.listen.belehrungen', ['nummer' => $t->nummer]))->assertOk();
    $als->get(route('admin.listen.belehrungen', ['nummer' => 999]))->assertNotFound();
});

it('wandelt die V1-Belehrung in Text mit Platzhaltern um', function () {
    $html = '<p><span>Ware muss am <span>DATUM</span> zwischen&nbsp;ABHOLUNG_AB und ABHOLUNG_BIS Uhr (ORT) abgeholt werden.</span></p>'
        ."\r\n".'<p><strong>25% spende ich.</strong></p>';

    expect(Belehrung::ausV1Html($html))
        ->toBe("Ware muss am {datum} zwischen {abholung_ab} und {abholung_bis} Uhr ({ort}) abgeholt werden.\n\n**25% spende ich.**");
});

it('füllt die Standard-Belehrung mit den Daten der Börse', function () {
    $boerse = neueBoerse(['abholung_beginn' => now()->setTime(14, 0), 'abholung_ende' => now()->setTime(15, 30)]);

    $text = Platzhalter::ersetzen(Belehrung::STANDARD, Platzhalter::fuer(null, $boerse));

    expect($text)->toContain('zwischen 14:00 und 15:30 Uhr')->not->toContain('{');
});
