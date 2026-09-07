<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\VKnummer;
use App\Model\Verkaufsartikel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerkaeuferPortalTest extends TestCase
{
    use RefreshDatabase;

    private function makeSellerWithVknummer(?Klamottenboerse $klamottenboerse = null, int $vknummer = 101): array
    {
        $klamottenboerse ??= Klamottenboerse::create([
            'datum' => now()->addDays(10)->toDateString(),
            'anmeldung' => now()->toDateString(),
            'anmeldungKinderhaus' => now()->toDateString(),
            'anlieferung_von' => '08:00:00',
            'anlieferung_bis' => '10:00:00',
            'abholung_von' => '18:00:00',
            'abholung_bis' => '19:00:00',
            'maxTeile' => 100,
        ]);

        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => Str::random(8).'@example.test',
        ]);

        $vknummerModel = VKnummer::create([
            'vknummer' => $vknummer,
            'klamottenboersen_id' => $klamottenboerse->id,
            'vergeben_an' => $interessent->id,
        ]);

        return [$interessent, $vknummerModel, $klamottenboerse];
    }

    public function test_seller_can_view_portal_with_valid_uuid()
    {
        [$interessent] = $this->makeSellerWithVknummer();

        $response = $this->get("/verkaeufer/{$interessent->uuid}");

        $response->assertOk();
        $response->assertSee('Verkäufer-Portal');
    }

    public function test_invalid_uuid_returns_404()
    {
        $response = $this->get('/verkaeufer/does-not-exist');

        $response->assertNotFound();
    }

    public function test_seller_can_add_and_list_an_article()
    {
        [$interessent, $vknummer] = $this->makeSellerWithVknummer();

        $response = $this->post("/verkaeufer/{$interessent->uuid}/artikel", [
            'beschreibung' => 'Blaue Jacke',
            'kategorie' => 'Jacken',
            'groesse' => '104',
            'preis' => 5.5,
        ]);

        $response->assertRedirect(route('verkaeuferPortal.index', ['uuid' => $interessent->uuid]));

        $this->assertDatabaseHas('verkaufsartikel', [
            'vknummer_id' => $vknummer->id,
            'artikelnummer' => 1,
            'beschreibung' => 'Blaue Jacke',
            'preis' => 5.5,
        ]);
    }

    public function test_article_numbers_increment_sequentially_per_seller()
    {
        [$interessent, $vknummer] = $this->makeSellerWithVknummer();

        $this->post("/verkaeufer/{$interessent->uuid}/artikel", ['beschreibung' => 'Artikel 1', 'preis' => 1]);
        $this->post("/verkaeufer/{$interessent->uuid}/artikel", ['beschreibung' => 'Artikel 2', 'preis' => 2]);

        $numbers = Verkaufsartikel::where('vknummer_id', $vknummer->id)->orderBy('artikelnummer')->pluck('artikelnummer')->all();

        $this->assertEquals([1, 2], $numbers);
    }

    public function test_seller_can_delete_own_article_but_not_others()
    {
        [$interessent, $vknummer, $klamottenboerse] = $this->makeSellerWithVknummer();
        [$otherInteressent, $otherVknummer] = $this->makeSellerWithVknummer($klamottenboerse, 102);

        $artikel = Verkaufsartikel::create([
            'vknummer_id' => $vknummer->id,
            'artikelnummer' => 1,
            'beschreibung' => 'Zum Löschen',
            'preis' => 3,
        ]);

        $foreignArtikel = Verkaufsartikel::create([
            'vknummer_id' => $otherVknummer->id,
            'artikelnummer' => 1,
            'beschreibung' => 'Fremder Artikel',
            'preis' => 3,
        ]);

        // Fremden Artikel löschen -> verboten
        $response = $this->delete("/verkaeufer/{$interessent->uuid}/artikel/{$foreignArtikel->id}");
        $response->assertForbidden();
        $this->assertDatabaseHas('verkaufsartikel', ['id' => $foreignArtikel->id]);

        // Eigenen Artikel löschen -> erlaubt
        $response = $this->delete("/verkaeufer/{$interessent->uuid}/artikel/{$artikel->id}");
        $response->assertRedirect(route('verkaeuferPortal.index', ['uuid' => $interessent->uuid]));
        $this->assertSoftDeleted('verkaufsartikel', ['id' => $artikel->id]);
    }

    public function test_label_printing_view_lists_all_articles()
    {
        [$interessent, $vknummer] = $this->makeSellerWithVknummer();

        Verkaufsartikel::create([
            'vknummer_id' => $vknummer->id,
            'artikelnummer' => 1,
            'beschreibung' => 'Hose',
            'preis' => 4,
        ]);

        $response = $this->get("/verkaeufer/{$interessent->uuid}/etiketten");

        $response->assertOk();
        $response->assertSee('Hose');
        $response->assertSee((string) $vknummer->vknummer);
    }

    public function test_live_sales_view_is_hidden_until_released_by_organizer()
    {
        [$interessent, $vknummer] = $this->makeSellerWithVknummer();

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $verkauf = \App\Model\verkaeufe::create([
            'user_id' => 1,
            'summe' => 3.5,
            'klamottenboerse_id' => $vknummer->klamottenboersen_id,
        ]);
        $verkauft = new \App\Model\verkaufteartikel();
        $verkauft->timestamps = false;
        $verkauft->forceFill([
            'verkauf' => $verkauf->id,
            'vknummer' => $vknummer->vknummer,
            'artikelnummer' => 1,
            'betrag' => 3.5,
            'klamottenboerse_id' => $vknummer->klamottenboersen_id,
        ])->save();

        $response = $this->get("/verkaeufer/{$interessent->uuid}");

        $response->assertOk();
        $response->assertDontSee('3,50 €');
    }

    public function test_live_sales_view_shows_current_revenue_once_released()
    {
        [$interessent, $vknummer] = $this->makeSellerWithVknummer();
        $vknummer->Klamottenboerse->update(['live_verkaufsansicht_freigabe' => true]);

        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $verkauf = \App\Model\verkaeufe::create([
            'user_id' => 1,
            'summe' => 3.5,
            'klamottenboerse_id' => $vknummer->klamottenboersen_id,
        ]);
        $verkauft = new \App\Model\verkaufteartikel();
        $verkauft->timestamps = false;
        $verkauft->forceFill([
            'verkauf' => $verkauf->id,
            'vknummer' => $vknummer->vknummer,
            'artikelnummer' => 1,
            'betrag' => 3.5,
            'klamottenboerse_id' => $vknummer->klamottenboersen_id,
        ])->save();

        $response = $this->get("/verkaeufer/{$interessent->uuid}");

        $response->assertOk();
        $response->assertSee('3,50 €');
    }
}
