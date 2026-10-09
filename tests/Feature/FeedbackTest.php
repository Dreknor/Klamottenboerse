<?php

use App\Models\FeedbackFrage;
use App\Models\Person;
use Illuminate\Support\Str;

it('hat die drei bisherigen Fragen als Standard', function () {
    expect(FeedbackFrage::query()->sortiert()->pluck('typ')->all())->toBe(['sterne', 'text', 'text'])
        ->and(FeedbackFrage::gesamtnote()->text)->toBe('Wie zufrieden warst du insgesamt?');
});

it('lässt das Orga-Team Fragen anlegen, ändern und ausblenden', function () {
    $boerse = neueBoerse();
    $orga = alsAdmin($this);

    $orga->get(route('admin.feedback.fragen.index'))->assertOk()->assertSee('Was können wir besser machen?');

    $orga->post(route('admin.feedback.fragen.store'), ['text' => 'Woher kennst du uns?', 'typ' => 'auswahl', 'optionen' => "Zeitung\nFreunde\n\nZeitung"])
        ->assertSessionHas('erfolg');
    $auswahl = FeedbackFrage::query()->where('text', 'Woher kennst du uns?')->sole();
    expect($auswahl->optionen)->toBe(['Zeitung', 'Freunde']);

    $orga->post(route('admin.feedback.fragen.store'), ['text' => 'Nur eine?', 'typ' => 'auswahl', 'optionen' => 'Ja'])->assertSessionHasErrors('optionen');
    $orga->post(route('admin.feedback.fragen.store'), ['text' => 'Wie war die Schicht?', 'typ' => 'sterne', 'rolle' => 'helfer', 'pflicht' => '1'])
        ->assertSessionHas('erfolg');

    // Verkäufer sehen die Helferfrage nicht, Pflichtfragen werden geprüft
    $verkaeufer = $boerse->feedback()->create(['rolle' => 'verkaeufer', 'token' => Str::random(40)]);
    $helfer = $boerse->feedback()->create(['rolle' => 'helfer', 'token' => Str::random(40)]);
    $this->get(route('feedback.show', $verkaeufer->token))->assertSee('Freunde')->assertDontSee('Wie war die Schicht?');
    $this->get(route('feedback.show', $helfer->token))->assertSee('Wie war die Schicht?');

    $schicht = FeedbackFrage::query()->where('text', 'Wie war die Schicht?')->sole();
    $this->post(route('feedback.store', $helfer->token), ['antworten' => [$auswahl->id => 'Freunde']])->assertSessionHasErrors("antworten.{$schicht->id}");
    $this->post(route('feedback.store', $verkaeufer->token), ['antworten' => [$auswahl->id => 'Radio']])->assertSessionHasErrors("antworten.{$auswahl->id}");
    $this->post(route('feedback.store', $verkaeufer->token), ['antworten' => [$auswahl->id => 'Freunde']])->assertOk();

    $orga->get(route('admin.feedback.index'))->assertOk()->assertSee('Woher kennst du uns?')->assertSee('Freunde');

    // Mit Antworten: nicht löschbar, Typ fest – aber ausblendbar
    $orga->delete(route('admin.feedback.fragen.destroy', $auswahl))->assertSessionHas('fehler');
    $orga->put(route('admin.feedback.fragen.update', $auswahl), ['text' => 'Woher?', 'typ' => 'text'])->assertSessionHas('fehler');
    $orga->put(route('admin.feedback.fragen.update', $auswahl), ['text' => 'Woher?', 'typ' => 'auswahl', 'optionen' => "Zeitung\nFreunde", 'sortierung' => 5])
        ->assertSessionHas('erfolg');
    expect($auswahl->fresh())->aktiv->toBeFalse()->text->toBe('Woher?');
    $this->get(route('feedback.show', $verkaeufer->token))->assertDontSee('Woher?');

    // Ohne Antworten löschbar
    $orga->delete(route('admin.feedback.fragen.destroy', $schicht))->assertSessionHas('erfolg');
    expect(FeedbackFrage::find($schicht->id))->toBeNull();
});

it('lässt nur das Orga-Team die Fragen pflegen', function () {
    $kasse = tap(Person::factory()->create(['password' => 'geheim-geheim']))->assignRole('kasse');
    neueBoerse();

    $this->actingAs($kasse)->withSession(['login_art' => 'passwort'])->get(route('admin.feedback.index'))
        ->assertOk()->assertDontSee('Feedback-Fragen');
    $this->get(route('admin.feedback.fragen.index'))->assertForbidden();
});
