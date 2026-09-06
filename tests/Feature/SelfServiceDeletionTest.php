<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class SelfServiceDeletionTest extends TestCase
{
    use RefreshDatabase;

    private function makeInteressent(string $mail): Interessenten
    {
        return Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => $mail,
            'registration_source' => 'self-service',
        ]);
    }

    public function test_a_deletion_request_sends_a_confirmation_mail()
    {
        Mail::fake();
        $interessent = $this->makeInteressent('delete-me@example.test');

        $response = $this->post('/registrieren/loeschen', [
            'mail' => 'delete-me@example.test',
        ]);

        $response->assertRedirect(route('registrierung.loeschen'));
        Mail::assertSent(\App\Mail\InteressentLoeschungBestaetigen::class);
        $this->assertNull($interessent->fresh()->deletion_requested_at);
    }

    public function test_unknown_mail_does_not_leak_information()
    {
        Mail::fake();

        $response = $this->post('/registrieren/loeschen', [
            'mail' => 'unknown@example.test',
        ]);

        $response->assertRedirect(route('registrierung.loeschen'));
        Mail::assertNothingSent();
    }

    public function test_confirming_deletion_soft_deletes_the_interessent()
    {
        $interessent = $this->makeInteressent('delete-confirm@example.test');

        $url = URL::temporarySignedRoute(
            'registrierung.loeschung.bestaetigen',
            now()->addHours(48),
            ['interessent' => $interessent->id]
        );

        $response = $this->get($url);

        $response->assertOk();

        $this->assertSoftDeleted('interessenten', ['id' => $interessent->id]);
        $this->assertNotNull($interessent->fresh()->deletion_requested_at);
    }

    public function test_purge_command_hard_deletes_after_grace_period()
    {
        $interessent = $this->makeInteressent('purge-me@example.test');
        $interessent->deletion_requested_at = now()->subDays(31);
        $interessent->save();
        $interessent->delete();

        $stillTooRecent = $this->makeInteressent('too-recent@example.test');
        $stillTooRecent->deletion_requested_at = now()->subDays(5);
        $stillTooRecent->save();
        $stillTooRecent->delete();

        Artisan::call('interessenten:purge-deleted');

        $this->assertDatabaseMissing('interessenten', ['id' => $interessent->id]);
        $this->assertSoftDeleted('interessenten', ['id' => $stillTooRecent->id]);
    }
}
