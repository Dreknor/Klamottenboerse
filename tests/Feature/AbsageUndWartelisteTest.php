<?php

use App\Domain\Teilnahme\Actions\Absagen;
use App\Domain\Teilnahme\Actions\AngebotAnnehmen;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
use App\Enums\TeilnahmeStatus;
use App\Models\Nachricht;
use App\Models\Person;

it('bietet bei einer Absage den freien Platz der Warteliste an', function () {
    $boerse = neueBoerse(['kapazitaet' => 1]);
    $erste = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $warte = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);

    app(Absagen::class)($erste);

    $warte->refresh();
    expect($erste->fresh()->status)->toBe(TeilnahmeStatus::Abgesagt)
        ->and($erste->fresh()->nummer)->toBeNull()
        ->and($warte->status)->toBe(TeilnahmeStatus::Angeboten)
        ->and($warte->nummer)->toBe(200)
        ->and($warte->angebot_bis->isFuture())->toBeTrue()
        ->and(Nachricht::query()->where('typ', 'warteliste_angebot')->where('person_id', $warte->person_id)->exists())->toBeTrue();
});

it('teilt die Nummer zu, wenn das Angebot angenommen wird', function () {
    $boerse = neueBoerse(['kapazitaet' => 1]);
    $erste = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $warte = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    app(Absagen::class)($erste);

    app(AngebotAnnehmen::class)($warte->fresh());

    expect($warte->fresh()->status)->toBe(TeilnahmeStatus::Zugeteilt);
});

it('lässt abgelaufene Angebote verfallen und bietet dem Nächsten an', function () {
    $boerse = neueBoerse(['kapazitaet' => 1]);
    $erste = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $zweite = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    $dritte = app(Anmelden::class)($boerse, Person::factory()->create(), mailSenden: false);
    app(Absagen::class)($erste);

    $this->travel($boerse->angebot_stunden + 1)->hours();
    app(WartelisteNachruecken::class)($boerse);

    expect($zweite->fresh()->status)->toBe(TeilnahmeStatus::Abgesagt)
        ->and($dritte->fresh()->status)->toBe(TeilnahmeStatus::Angeboten);
});

it('erlaubt keine Absage der Kinderhaus-Nummer', function () {
    $boerse = neueBoerse();

    app(Absagen::class)($boerse->kinderhausTeilnahme);
})->throws(DomainException::class);

it('plant eine Bestätigungsmail mit Nummer und Absage-Link ein', function () {
    $boerse = neueBoerse();
    $person = Person::factory()->create(['vorname' => 'Mia']);

    $teilnahme = app(Anmelden::class)($boerse, $person);

    $mail = Nachricht::query()->where('person_id', $person->id)->sole();
    expect($mail->typ)->toBe('nummer_zugeteilt')
        ->and($mail->betreff)->toContain((string) $teilnahme->nummer)
        ->and($mail->inhalt)->toContain('Hallo Mia')
        ->and($mail->inhalt)->toContain('/teilnahme/'.$teilnahme->id.'/absage');
});
