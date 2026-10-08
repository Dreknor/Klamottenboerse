<?php

namespace Tests\Feature;

use App\Model\Einstellung;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\User;
use App\Model\VerkaeuferVermerk;
use App\Model\VKnummer;
use App\Model\VermerkTyp;
use App\Model\Warteliste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class VerkaeuferReputationTest extends TestCase
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

    private function makeUser(array $rechte): User
    {
        return User::create(array_merge([
            'name' => 'Helfer',
            'email' => Str::random(8).'@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 0,
            'kasse' => 0,
        ], $rechte));
    }

    private function makeInteressent(): Interessenten
    {
        return Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => Str::random(8).'@example.test',
        ]);
    }

    private function vermerk(Interessenten $interessent, int $punkte, $erstellt = null): VerkaeuferVermerk
    {
        $vermerk = VerkaeuferVermerk::create([
            'interessent_id' => $interessent->id,
            'typ' => 'kiste_nicht_gebracht',
            'punkte' => $punkte,
        ]);

        if ($erstellt) {
            $vermerk->created_at = $erstellt;
            $vermerk->save();
        }

        return $vermerk;
    }

    public function test_kasse_can_record_incident_by_vknummer_with_default_points()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $interessent = $this->makeInteressent();
        VKnummer::create(['vknummer' => 301, 'klamottenboersen_id' => $klamottenboerse->id, 'vergeben_an' => $interessent->id]);

        $response = $this->actingAs($this->makeUser(['kasse' => 1]))
            ->from('/kasse')
            ->post('/vermerke', [
                'vknummer' => 301,
                'typ' => 'defekte_ware',
                'punkte' => 9, // wird für Kassen-Accounts ignoriert
                'quelle' => 'kasse',
            ]);

        $response->assertRedirect('/kasse');
        $this->assertDatabaseHas('verkaeufer_vermerke', [
            'interessent_id' => $interessent->id,
            'typ' => 'defekte_ware',
            'punkte' => VerkaeuferVermerk::standardPunkte('defekte_ware'),
            'quelle' => 'kasse',
        ]);
    }

    public function test_unknown_vknummer_is_rejected()
    {
        $this->makeKlamottenboerse();

        $this->actingAs($this->makeUser(['kasse' => 1]))
            ->from('/kasse')
            ->post('/vermerke', ['vknummer' => 999, 'typ' => 'sonstiges'])
            ->assertRedirect('/kasse')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('verkaeufer_vermerke', 0);
    }

    public function test_users_without_kasse_or_verwaltung_cannot_record_incidents()
    {
        $this->makeKlamottenboerse();
        $interessent = $this->makeInteressent();

        $this->actingAs($this->makeUser([]))
            ->post('/vermerke', ['interessent_id' => $interessent->id, 'typ' => 'sonstiges'])
            ->assertRedirect(url('/'));

        $this->assertDatabaseCount('verkaeufer_vermerke', 0);
    }

    public function test_reputation_threshold_and_expiry()
    {
        Einstellung::setze('reputation_sperre_ab', 5);
        Einstellung::setze('reputation_zeitraum_monate', 24);
        $interessent = $this->makeInteressent();

        $this->vermerk($interessent, 3);
        $this->vermerk($interessent, 3, now()->subMonths(30)); // verjährt
        $this->assertFalse($interessent->istAutomatischeVergabeGesperrt());

        $this->vermerk($interessent, 2);
        $this->assertTrue($interessent->istAutomatischeVergabeGesperrt());

        $interessent->update(['nur_manuelle_vergabe' => false]);
        $this->assertFalse($interessent->fresh()->istAutomatischeVergabeGesperrt());
    }

    public function test_waitlist_automation_skips_sellers_with_bad_reputation()
    {
        Mail::fake();
        Einstellung::setze('reputation_sperre_ab', 5);

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create(['vknummer' => 501, 'klamottenboersen_id' => $klamottenboerse->id]);

        $gesperrt = $this->makeInteressent();
        $this->vermerk($gesperrt, 6);
        $gesperrterEintrag = Warteliste::create(['interessenten_id' => $gesperrt->id]);
        $gesperrterEintrag->created_at = now()->subDay();
        $gesperrterEintrag->save();

        $naechster = $this->makeInteressent();
        Warteliste::create(['interessenten_id' => $naechster->id]);

        $this->artisan('warteliste:nachruecken')->assertExitCode(0);

        $this->assertEquals($naechster->id, $vknummer->fresh()->reserviert_fuer);
        $this->assertNull($gesperrterEintrag->fresh()->angebotene_vknummer_id);
    }

    public function test_manual_lock_overrides_points()
    {
        Mail::fake();

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create(['vknummer' => 502, 'klamottenboersen_id' => $klamottenboerse->id]);
        $interessent = $this->makeInteressent();
        Warteliste::create(['interessenten_id' => $interessent->id]);

        $this->actingAs($this->makeUser(['verwaltung' => 1]))
            ->put("/interessenten/{$interessent->id}/vergabemodus", ['modus' => 'manuell'])
            ->assertSessionHas('success');

        $this->artisan('warteliste:nachruecken')->assertExitCode(0);

        $this->assertNull($vknummer->fresh()->reserviert_fuer);
    }

    public function test_overview_and_quick_access_pages_render()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $interessent = $this->makeInteressent();
        $this->vermerk($interessent, 6);

        $admin = $this->makeUser(['verwaltung' => 1, 'kasse' => 1]);

        $this->actingAs($admin)->get('/vermerke')
            ->assertOk()
            ->assertSee('Muster')
            ->assertSee('nur manuelle Vergabe');

        $this->actingAs($admin)->get('/vermerke/erfassen?vknummer=301')->assertOk()->assertSee('301');
        $this->actingAs($admin)->get('/kisten')->assertOk()->assertSee('vermerkModal');
        $this->actingAs($admin)->get('/kasse')->assertOk()->assertSee('Vorfall zu Verkäufer melden');
        $this->actingAs($admin)->get('/interessent/'.$interessent->id)
            ->assertOk()
            ->assertSee('Keine automatische Nummernvergabe')
            ->assertSee('Angebot des Verkäufers');
    }

    public function test_team_can_configure_thresholds_and_points()
    {
        $admin = $this->makeUser(['verwaltung' => 1]);
        $interessent = $this->makeInteressent();
        $this->vermerk($interessent, 2);
        VerkaeuferVermerk::create(['interessent_id' => $interessent->id, 'typ' => 'defekte_ware', 'punkte' => 2]);

        $this->actingAs($admin)->get('/einstellungen/reputation')->assertOk()->assertSee('Defekte / verschmutzte Ware');

        $this->actingAs($admin)->put('/einstellungen/reputation', [
            'sperre_ab' => 8,
            'warnung_ab' => 3,
            'zeitraum_monate' => 12,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(8, Einstellung::zahl('reputation_sperre_ab'));
        $this->assertEquals(12, Einstellung::zahl('reputation_zeitraum_monate'));

        $typ = VermerkTyp::where('schluessel', 'defekte_ware')->first();
        $this->actingAs($admin)->put("/einstellungen/reputation/typen/{$typ->id}", [
            'label' => 'Kaputte Ware',
            'punkte' => 4,
            'sortierung' => 5,
            'aktiv' => 1,
            'bestehende_anpassen' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertEquals(4, VerkaeuferVermerk::standardPunkte('defekte_ware'));
        $this->assertDatabaseHas('verkaeufer_vermerke', ['typ' => 'defekte_ware', 'punkte' => 4]);
        $this->assertDatabaseHas('verkaeufer_vermerke', ['typ' => 'kiste_nicht_gebracht', 'punkte' => 2]);
        $this->assertEquals(6, $interessent->reputationPunkte());
    }

    public function test_threshold_validation_and_new_or_deactivated_types()
    {
        $admin = $this->makeUser(['verwaltung' => 1]);

        $this->actingAs($admin)->put('/einstellungen/reputation', [
            'sperre_ab' => 2, 'warnung_ab' => 3, 'zeitraum_monate' => 12,
        ])->assertSessionHasErrors('warnung_ab');

        $this->actingAs($admin)->post('/einstellungen/reputation/typen', [
            'label' => 'Kiste beschaedigt zurueckgelassen', 'punkte' => 1,
        ])->assertSessionHasNoErrors();
        $neu = VermerkTyp::where('label', 'Kiste beschaedigt zurueckgelassen')->first();
        $this->assertNotNull($neu);
        $this->assertTrue(VerkaeuferVermerk::typen()->has($neu->schluessel));

        // Deaktivieren (Checkbox "aktiv" nicht gesendet) => nicht mehr erfassbar
        $this->actingAs($admin)->put("/einstellungen/reputation/typen/{$neu->id}", [
            'label' => $neu->label, 'punkte' => 1,
        ]);
        $this->assertFalse(VerkaeuferVermerk::typen()->has($neu->schluessel));

        $interessent = $this->makeInteressent();
        $this->actingAs($admin)->post('/vermerke', ['interessent_id' => $interessent->id, 'typ' => $neu->schluessel])
            ->assertSessionHasErrors('typ');
    }

    public function test_settings_are_not_accessible_for_kasse_only_accounts()
    {
        $kasse = $this->makeUser(['kasse' => 1]);

        $this->actingAs($kasse)->get('/einstellungen/reputation')->assertRedirect(url('/'));
        $this->actingAs($kasse)->get('/vermerke')->assertRedirect(url('/'));
        $this->actingAs($kasse)->put('/einstellungen/reputation', ['sperre_ab' => 1, 'warnung_ab' => 1, 'zeitraum_monate' => 1])
            ->assertRedirect(url('/'));
        $this->assertEquals(5, Einstellung::zahl('reputation_sperre_ab'));
    }

    public function test_reputation_is_not_visible_to_the_seller()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $interessent = $this->makeInteressent();
        VKnummer::create(['vknummer' => 303, 'klamottenboersen_id' => $klamottenboerse->id, 'vergeben_an' => $interessent->id]);
        VerkaeuferVermerk::create(['interessent_id' => $interessent->id, 'typ' => 'defekte_ware', 'punkte' => 9, 'bemerkung' => 'GEHEIME-BEMERKUNG']);

        $response = $this->get('/verkaeufer/'.$interessent->uuid)->assertOk();

        foreach (['GEHEIME-BEMERKUNG', 'Reputation', 'Vermerk', 'Vorfall', 'manuelle Vergabe', 'Pkt.'] as $text) {
            $response->assertDontSee($text);
        }
    }
}
