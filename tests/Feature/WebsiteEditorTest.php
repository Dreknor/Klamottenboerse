<?php

use App\Models\Person;
use App\Models\Seite;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function websiteOrga($test)
{
    return $test->actingAs(tap(Person::factory()->create())->assignRole('orga'))->withSession(['login_art' => 'passwort']);
}

it('zeigt Startseite, Infoseiten und Menü aus Bausteinen', function () {
    neueBoerse(['titel' => 'Testbörse']);

    $this->get('/')->assertOk()->assertSee('Sortierter Kindersachenflohmarkt')->assertSee('So läuft es ab')->assertSee('Nächste Klamottenbörse');
    $this->get('/faq')->assertOk()->assertSee('Häufige Fragen')->assertSee('25 % deines Erlöses');
    $this->get('/verkaeufer-info')->assertOk()->assertSee('Etiketten');
    $this->get('/')->assertSee(route('seite', 'faq'))->assertSee('Fragen &amp; Antworten', false);
    $this->get('/gibt-es-nicht')->assertNotFound();
});

it('speichert Entwürfe, zeigt Vorschau und veröffentlicht mit Version', function () {
    $faq = Seite::where('slug', 'faq')->sole();
    $als = websiteOrga($this);
    $bloecke = json_encode([['typ' => 'hinweis', 'titel' => 'Neu!', 'text' => 'Wir suchen **Helfer**', 'farbe' => 'orange', 'unbekannt' => 'weg'], ['typ' => 'boese']]);

    $als->get(route('admin.seiten.edit', $faq))->assertOk()->assertSee('Baustein hinzufügen');
    $als->put(route('admin.seiten.update', $faq), ['titel' => 'FAQ neu', 'bloecke' => $bloecke, 'aktion' => 'speichern'])->assertSessionHas('erfolg');

    expect($faq->fresh()->entwurf['bloecke'])->toBe([['typ' => 'hinweis', 'titel' => 'Neu!', 'text' => 'Wir suchen **Helfer**', 'farbe' => 'orange']]);
    $this->get('/faq')->assertDontSee('Wir suchen'); // Besucher sehen noch die alte Fassung
    $als->get(route('admin.seiten.vorschau', $faq))->assertOk()->assertSee('Vorschau des Entwurfs')->assertSee('<strong>Helfer</strong>', false);

    $als->put(route('admin.seiten.update', $faq), ['titel' => 'FAQ neu', 'bloecke' => $bloecke, 'aktion' => 'veroeffentlichen']);
    $this->get('/faq')->assertSee('Wir suchen')->assertSee('FAQ neu');

    // frühere Fassung zurückholen
    $version = $faq->versionen()->first();
    expect($faq->fresh()->entwurf)->toBeNull()->and($version)->not->toBeNull();
});

it('legt neue Seiten an, die erst nach dem Veröffentlichen sichtbar sind', function () {
    $als = websiteOrga($this);

    $als->post(route('admin.seiten.store'), ['titel' => 'Spenden & Förderverein'])->assertRedirect();
    $seite = Seite::where('slug', 'spenden-foerderverein')->sole();
    $this->get('/spenden-foerderverein')->assertNotFound();

    $als->put(route('admin.seiten.update', $seite), ['titel' => 'Spenden', 'aktion' => 'veroeffentlichen',
        'bloecke' => json_encode([['typ' => 'text', 'titel' => '', 'text' => 'Danke an alle Spender']])]);
    $als->put(route('admin.seiten.einstellungen', $seite), ['im_menue' => '1', 'menue_reihenfolge' => 5]);

    $this->get('/spenden-foerderverein')->assertOk()->assertSee('Danke an alle Spender');
    $this->get('/')->assertSee(route('seite', 'spenden-foerderverein'));

    $als->post(route('admin.seiten.store'), ['titel' => 'Admin'])->assertSessionHasErrors('titel');
    $als->delete(route('admin.seiten.destroy', Seite::where('slug', 'impressum')->sole()))->assertForbidden();
});

it('lädt Bilder mit Pflicht-Beschreibung hoch und zeigt sie im Bild-Baustein', function () {
    Storage::fake('public');
    $start = Seite::where('slug', 'start')->sole();
    $als = websiteOrga($this);

    $als->post(route('admin.seiten.bild', $start), ['bild' => UploadedFile::fake()->image('saal.jpg', 2400, 1600)])->assertSessionHasErrors('alt');
    $als->post(route('admin.seiten.bild', $start), ['bild' => UploadedFile::fake()->image('saal.jpg', 2400, 1600), 'alt' => 'Der Saal'])->assertSessionHas('erfolg');
    $bild = $start->getMedia('bilder')->sole();
    expect($bild->hasGeneratedConversion('web'))->toBeTrue();

    $als->put(route('admin.seiten.update', $start), ['titel' => 'Startseite', 'aktion' => 'veroeffentlichen',
        'bloecke' => json_encode([['typ' => 'bild', 'media_id' => $bild->id, 'alt' => 'Der Saal', 'breite' => 'voll']])]);

    $this->get('/')->assertSee('alt="Der Saal"', false)->assertSee($bild->getUrl('web'));
});
