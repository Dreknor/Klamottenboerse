<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\Mailvorlagen;
use App\Model\VKnummer;
use App\Model\Warteliste;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class WartelisteAutomationTest extends TestCase
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

    public function test_free_vknummer_is_offered_to_oldest_waiting_candidate()
    {
        Mail::fake();

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create([
            'vknummer' => 501,
            'klamottenboersen_id' => $klamottenboerse->id,
        ]);

        $interessent = $this->makeInteressent();
        $eintrag = Warteliste::create(['interessenten_id' => $interessent->id]);

        $this->artisan('warteliste:nachruecken')->assertExitCode(0);

        $eintrag->refresh();
        $vknummer->refresh();

        $this->assertEquals($vknummer->id, $eintrag->angebotene_vknummer_id);
        $this->assertNotNull($eintrag->token);
        $this->assertNotNull($eintrag->angebot_ablauf_at);
        $this->assertEquals($interessent->id, $vknummer->reserviert_fuer);

        Mail::assertSent(\App\Mail\WartelisteAngebotMail::class);
    }

    public function test_confirming_offer_assigns_vknummer_and_removes_waitlist_entry()
    {
        Mail::fake();

        Mailvorlagen::create([
            'name' => 'VerkäuferInfos',
            'betreff' => 'Deine VK-Nummer',
            'text' => 'Hallo VORNAME',
            'html' => '<p>Hallo VORNAME</p>',
        ]);

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create([
            'vknummer' => 502,
            'klamottenboersen_id' => $klamottenboerse->id,
        ]);

        $interessent = $this->makeInteressent();
        Warteliste::create(['interessenten_id' => $interessent->id]);

        $this->artisan('warteliste:nachruecken')->assertExitCode(0);

        $eintrag = Warteliste::first();
        $token = $eintrag->token;

        $response = $this->get("/warteliste/{$token}/bestaetigen");

        $response->assertOk();

        $vknummer->refresh();
        $this->assertEquals($interessent->id, $vknummer->vergeben_an);
        $this->assertDatabaseMissing('warteliste', ['id' => $eintrag->id]);
    }

    public function test_expired_offer_frees_slot_and_skiplists_vknummer()
    {
        Mail::fake();

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create([
            'vknummer' => 503,
            'klamottenboersen_id' => $klamottenboerse->id,
        ]);

        $interessent = $this->makeInteressent();
        $eintrag = Warteliste::create([
            'interessenten_id' => $interessent->id,
            'angebotene_vknummer_id' => $vknummer->id,
            'angebot_versendet_at' => now()->subHours(50),
            'angebot_ablauf_at' => now()->subHours(2),
            'token' => Str::random(48),
        ]);

        $vknummer->update(['reserviert_fuer' => $interessent->id]);

        $this->artisan('warteliste:angebote-bereinigen')->assertExitCode(0);

        $eintrag->refresh();
        $vknummer->refresh();

        $this->assertNull($vknummer->reserviert_fuer);
        $this->assertNull($eintrag->angebotene_vknummer_id);
        $this->assertNull($eintrag->token);
        $this->assertContains($vknummer->id, $eintrag->uebersprungene_vknummern);
    }

    public function test_next_candidate_gets_offered_after_previous_offer_expired()
    {
        Mail::fake();

        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = VKnummer::create([
            'vknummer' => 504,
            'klamottenboersen_id' => $klamottenboerse->id,
        ]);

        $first = $this->makeInteressent();
        $firstEintrag = Warteliste::create([
            'interessenten_id' => $first->id,
            'created_at' => now()->subDay(),
        ]);

        $second = $this->makeInteressent();
        Warteliste::create(['interessenten_id' => $second->id]);

        // Erstes Angebot -> geht an den ältesten Eintrag ($first)
        $this->artisan('warteliste:nachruecken')->assertExitCode(0);
        $firstEintrag->refresh();
        $this->assertEquals($vknummer->id, $firstEintrag->angebotene_vknummer_id);

        // Angebot künstlich ablaufen lassen
        $firstEintrag->update(['angebot_ablauf_at' => now()->subMinute()]);

        $this->artisan('warteliste:angebote-bereinigen')->assertExitCode(0);
        $this->artisan('warteliste:nachruecken')->assertExitCode(0);

        $firstEintrag->refresh();
        $secondEintrag = Warteliste::where('interessenten_id', $second->id)->first();
        $vknummer->refresh();

        // Erste Person bekommt diese Nummer nicht erneut angeboten
        $this->assertNull($firstEintrag->angebotene_vknummer_id);
        $this->assertContains($vknummer->id, $firstEintrag->uebersprungene_vknummern);

        // Zweite Person bekommt nun das Angebot
        $this->assertEquals($vknummer->id, $secondEintrag->angebotene_vknummer_id);
        $this->assertEquals($second->id, $vknummer->reserviert_fuer);
    }
}
