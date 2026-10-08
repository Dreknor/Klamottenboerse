<?php

namespace Tests\Feature;

use App\Model\Angebotskategorie;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\User;
use App\Model\Verkaufsartikel;
use App\Model\VKnummer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class AngebotskategorienTest extends TestCase
{
    use RefreshDatabase;

    private function makeKlamottenboerse(): Klamottenboerse
    {
        return Klamottenboerse::create([
            'datum' => now()->addDays(10)->toDateString(),
            'anmeldung' => now()->toDateString(),
            'anmeldungKinderhaus' => now()->toDateString(),
            'anlieferung_von' => '08:00:00',
            'anlieferung_bis' => '10:00:00',
            'abholung_von' => '18:00:00',
            'abholung_bis' => '19:00:00',
            'maxTeile' => 100,
        ]);
    }

    private function makeVerkaeufer(Klamottenboerse $klamottenboerse, array $kategorien = []): VKnummer
    {
        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => Str::random(8).'@example.test',
            'angebotskategorien' => $kategorien,
        ]);

        return VKnummer::create([
            'vknummer' => 401,
            'klamottenboersen_id' => $klamottenboerse->id,
            'vergeben_an' => $interessent->id,
        ]);
    }

    public function test_categories_are_stored_on_registration()
    {
        Mail::fake();

        $this->get('/registrieren')->assertOk()->assertSee('kat_spielzeug');

        $this->post('/registrieren', [
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'maria@example.test',
            'angebotskategorien' => ['spielzeug', 'groesse_74_92'],
            'form_rendered_at' => time() - 10,
        ])->assertSessionHasNoErrors();

        $interessent = Interessenten::where('mail', 'maria@example.test')->first();
        $this->assertEquals(['spielzeug', 'groesse_74_92'], $interessent->angebotskategorien);
    }

    public function test_unknown_category_is_rejected_on_registration()
    {
        Mail::fake();

        $this->post('/registrieren', [
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'maria@example.test',
            'angebotskategorien' => ['gibtsnicht'],
            'form_rendered_at' => time() - 10,
        ])->assertSessionHasErrors('angebotskategorien.0');
    }

    public function test_seller_can_update_categories_in_portal()
    {
        $vknummer = $this->makeVerkaeufer($this->makeKlamottenboerse());
        $uuid = $vknummer->vergeben_an_Interessent->uuid;

        $this->post("/verkaeufer/{$uuid}/kategorien", ['angebotskategorien' => ['buecher']])
            ->assertRedirect(route('verkaeuferPortal.index', ['uuid' => $uuid]));

        $this->assertEquals(['buecher'], $vknummer->vergeben_an_Interessent->fresh()->angebotskategorien);
    }

    public function test_overview_aggregates_declared_categories_and_articles()
    {
        $vknummer = $this->makeVerkaeufer($this->makeKlamottenboerse(), ['spielzeug']);

        Verkaufsartikel::create(['vknummer_id' => $vknummer->id, 'artikelnummer' => 1, 'beschreibung' => 'Body', 'groesse' => '86/92', 'preis' => 2]);
        Verkaufsartikel::create(['vknummer_id' => $vknummer->id, 'artikelnummer' => 2, 'beschreibung' => 'Puzzle', 'kategorie' => 'spielzeug', 'preis' => 3]);

        $admin = User::create([
            'name' => 'Admin',
            'email' => Str::random(8).'@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/angebote');

        $response->assertOk()->assertSee('Spielzeug &amp; Spiele', false)->assertSee('401');

        $kategorien = $response->viewData('kategorien');
        $this->assertEquals(1, $kategorien['spielzeug']['verkaeufer']->count());
        $this->assertEquals(1, $kategorien['spielzeug']['artikel']);
        $this->assertEquals(1, $kategorien['groesse_74_92']['artikel']);
    }

    public function test_team_can_add_edit_and_deactivate_categories()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => Str::random(8).'@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 1,
        ]);

        $this->actingAs($admin)->get('/einstellungen/kategorien')->assertOk()->assertSee('Spielzeug');

        $this->actingAs($admin)->post('/einstellungen/kategorien', [
            'label' => 'Faschingskostueme',
            'gruppe' => 'Weiteres',
        ])->assertSessionHasNoErrors();

        $neu = Angebotskategorie::where('label', 'Faschingskostueme')->firstOrFail();
        $this->get('/registrieren')->assertSee('Faschingskostueme');

        // Groessenbereich muss vollstaendig und aufsteigend sein
        $this->actingAs($admin)->put("/einstellungen/kategorien/{$neu->id}", [
            'label' => 'Faschingskostueme', 'gruppe' => 'Weiteres', 'groesse_von' => 100, 'groesse_bis' => 50, 'aktiv' => 1,
        ])->assertSessionHasErrors('groesse_bis');

        // Deaktivieren: nicht mehr auswaehlbar, Label fuer bestehende Angaben bleibt
        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(), 'anrede' => 'Frau', 'vorname' => 'A', 'nachname' => 'B',
            'mail' => Str::random(8).'@example.test', 'angebotskategorien' => [$neu->schluessel],
        ]);
        $this->actingAs($admin)->put("/einstellungen/kategorien/{$neu->id}", [
            'label' => 'Kostuemkiste', 'gruppe' => 'Weiteres',
        ])->assertSessionHasNoErrors();

        $this->get('/registrieren')->assertDontSee('Kostuemkiste');
        $this->assertEquals(['Kostuemkiste'], $interessent->fresh()->angebotskategorienLabels());
    }
}
