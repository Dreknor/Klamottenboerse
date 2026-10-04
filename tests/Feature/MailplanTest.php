<?php

use App\Domain\Boersen\Actions\BoerseKopieren;
use App\Domain\Kommunikation\MailplanAusfuehren;
use App\Domain\Kommunikation\Postausgang;
use App\Domain\Teilnahme\Actions\Anmelden;
use App\Enums\NachrichtStatus;
use App\Models\Feedback;
use App\Models\Nachricht;
use App\Models\Person;
use App\Models\Schicht;
use App\Support\Einstellungen;
use Illuminate\Support\Facades\Mail;

it('legt für neue Börsen Standard-Mailplan und Checkliste an', function () {
    $boerse = neueBoerse();

    expect($boerse->mailplan()->count())->toBe(5)
        ->and($boerse->aufgaben()->count())->toBeGreaterThan(10)
        ->and($boerse->aufgaben()->orderBy('faellig_am')->first()->faellig_am->toDateString())
        ->toBe($boerse->verkaufstag->copy()->subDays(90)->toDateString());
});

it('schreibt Kinderhaus-Familien zuerst an und niemanden doppelt', function () {
    $boerse = neueBoerse();
    $familie = Person::factory()->kinderhaus()->create();
    $andere = Person::factory()->create();
    Person::factory()->create(['info_mails_erlaubt_at' => null]); // ohne Einwilligung

    (new MailplanAusfuehren)();
    (new MailplanAusfuehren)();

    $empfaenger = Nachricht::query()->where('typ', 'anmeldung_moeglich')->pluck('person_id');
    expect($empfaenger->sort()->values()->all())->toBe([$familie->id, $andere->id]);
});

it('schreibt bereits angemeldete Personen nicht mehr zur Anmeldung an', function () {
    $boerse = neueBoerse();
    $angemeldet = Person::factory()->create();
    app(Anmelden::class)($boerse, $angemeldet, mailSenden: false);

    (new MailplanAusfuehren)();

    expect(Nachricht::query()->where('typ', 'anmeldung_moeglich')->where('person_id', $angemeldet->id)->exists())->toBeFalse();
});

it('erzeugt persönliche Feedback-Links für Verkäufer und Helfer', function () {
    $boerse = neueBoerse(['verkaufstag' => now()->subDays(3)->toDateString()]);
    $verkaeufer = Person::factory()->create();
    app(Anmelden::class)($boerse, $verkaeufer, mailSenden: false);
    $helfer = Person::factory()->ohneMail()->create(); // ohne Mail → keine Mail, kein Fehler
    $schicht = Schicht::create(['boerse_id' => $boerse->id, 'bereich' => 'Kasse', 'beginn' => now(), 'ende' => now()->addHour()]);
    $schicht->einteilungen()->create(['person_id' => $helfer->id]);

    $eintrag = $boerse->mailplan()->whereHas('vorlage', fn ($q) => $q->where('schluessel', 'feedback'))->sole();
    (new MailplanAusfuehren)->eintragAusfuehren($eintrag);

    $feedback = Feedback::sole();
    expect($feedback->person_id)->toBe($verkaeufer->id)
        ->and(Nachricht::query()->where('typ', 'feedback')->sole()->inhalt)->toContain($feedback->token);
});

it('hält das einstellbare Stundenlimit für Mails ein', function () {
    Mail::fake();
    Einstellungen::set('mail_max_pro_stunde', 3);
    Person::factory()->count(5)->create()->each(fn ($p) => Postausgang::einplanen($p, 'login_link'));

    expect(Postausgang::versendeFaellige())->toBe(3)
        ->and(Postausgang::versendeFaellige())->toBe(0)
        ->and(Nachricht::query()->where('status', NachrichtStatus::Wartend)->count())->toBe(2);

    $this->travel(61)->minutes();
    expect(Postausgang::versendeFaellige())->toBe(2);
});

it('kopiert eine Börse mit verschobenen Terminen, Schichten und Mailplan', function () {
    $alt = neueBoerse(['kapazitaet' => 180, 'max_teile' => 50]);
    Schicht::create(['boerse_id' => $alt->id, 'bereich' => 'Annahme',
        'beginn' => $alt->anlieferung_beginn, 'ende' => $alt->anlieferung_ende, 'soll' => 4]);
    $alt->mailplan()->first()->update(['aktiv' => false]);

    $neuerTag = $alt->verkaufstag->copy()->addDays(182);
    $neu = app(BoerseKopieren::class)($alt, $neuerTag);

    expect($neu->kapazitaet)->toBe(180)
        ->and($neu->max_teile)->toBe(50)
        ->and($neu->anlieferung_beginn->equalTo($alt->anlieferung_beginn->copy()->addDays(182)))->toBeTrue()
        ->and($neu->schichten()->sole()->soll)->toBe(4)
        ->and($neu->mailplan()->count())->toBe(5)
        ->and($neu->mailplan()->where('aktiv', false)->count())->toBe(1)
        ->and($neu->aufgaben()->count())->toBe($alt->aufgaben()->count())
        ->and($neu->kinderhausTeilnahme->nummer)->toBe(600);
});
