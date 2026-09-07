<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use App\Model\Kiste;
use App\Model\Klamottenboerse;
use App\Model\User;
use App\Model\VKnummer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class KistenCheckinCheckoutTest extends TestCase
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

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => Str::random(8).'@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 1,
        ]);
    }

    private function makeVknummer(Klamottenboerse $klamottenboerse): VKnummer
    {
        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => Str::random(8).'@example.test',
        ]);

        return VKnummer::create([
            'vknummer' => 701,
            'klamottenboersen_id' => $klamottenboerse->id,
            'vergeben_an' => $interessent->id,
        ]);
    }

    public function test_admin_can_check_in_multiple_boxes_for_a_seller()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = $this->makeVknummer($klamottenboerse);
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->post('/kisten', [
            'vknummer_id' => $vknummer->id,
            'anzahl' => 3,
            'bemerkung' => 'Testabgabe',
        ]);

        $response->assertRedirect(route('kisten.index'));

        $this->assertDatabaseCount('kisten', 3);
        $this->assertDatabaseHas('kisten', [
            'vknummer_id' => $vknummer->id,
            'kistennummer' => 1,
            'status' => 'abgegeben',
        ]);
        $this->assertDatabaseHas('kisten', [
            'vknummer_id' => $vknummer->id,
            'kistennummer' => 3,
            'status' => 'abgegeben',
        ]);
    }

    public function test_admin_can_check_out_a_box()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = $this->makeVknummer($klamottenboerse);
        $admin = $this->makeAdmin();

        $kiste = Kiste::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'vknummer_id' => $vknummer->id,
            'kistennummer' => 1,
            'qr_token' => Kiste::generiereQrToken(),
            'status' => Kiste::STATUS_ABGEGEBEN,
            'abgegeben_at' => now(),
            'abgegeben_von' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/kisten/{$kiste->id}/checkout");

        $response->assertRedirect(route('kisten.index'));

        $kiste->refresh();
        $this->assertTrue($kiste->istAbgeholt());
        $this->assertNotNull($kiste->abgeholt_at);
        $this->assertEquals($admin->id, $kiste->abgeholt_von);
    }

    public function test_checking_out_an_already_checked_out_box_shows_error()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = $this->makeVknummer($klamottenboerse);
        $admin = $this->makeAdmin();

        $kiste = Kiste::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'vknummer_id' => $vknummer->id,
            'kistennummer' => 1,
            'qr_token' => Kiste::generiereQrToken(),
            'status' => Kiste::STATUS_ABGEHOLT,
            'abgegeben_at' => now()->subHour(),
            'abgeholt_at' => now(),
            'abgeholt_von' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post("/kisten/{$kiste->id}/checkout");

        $response->assertRedirect(route('kisten.index'));
        $response->assertSessionHas('error');
    }

    public function test_qr_code_scan_checks_out_the_box()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $vknummer = $this->makeVknummer($klamottenboerse);
        $admin = $this->makeAdmin();

        $kiste = Kiste::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'vknummer_id' => $vknummer->id,
            'kistennummer' => 1,
            'qr_token' => Kiste::generiereQrToken(),
            'status' => Kiste::STATUS_ABGEGEBEN,
            'abgegeben_at' => now(),
            'abgegeben_von' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->get("/kisten/scan/{$kiste->qr_token}");

        $response->assertRedirect(route('kisten.index'));

        $kiste->refresh();
        $this->assertTrue($kiste->istAbgeholt());
    }

    public function test_guest_cannot_access_kisten_routes()
    {
        $response = $this->get('/kisten');

        $response->assertRedirect('/login');
    }
}
