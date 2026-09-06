<?php

namespace Tests\Feature;

use App\Model\Interessenten;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_visitor_can_register_as_interessent()
    {
        Mail::fake();

        $response = $this->post('/registrieren', [
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'maria@example.test',
            'telefon' => '',
            'handy' => '',
            'website' => '',
            'form_rendered_at' => time() - 10,
        ]);

        $response->assertRedirect(route('registrierung.create'));

        $this->assertDatabaseHas('interessenten', [
            'mail' => 'maria@example.test',
            'registration_source' => 'self-service',
        ]);

        Mail::assertSent(\App\Mail\InteressentRegistrierungBestaetigen::class);
    }

    public function test_registration_is_rejected_when_honeypot_is_filled()
    {
        Mail::fake();

        $response = $this->post('/registrieren', [
            'anrede' => 'Frau',
            'vorname' => 'Spam',
            'nachname' => 'Bot',
            'mail' => 'spam@example.test',
            'website' => 'https://spam.example',
            'form_rendered_at' => time() - 10,
        ]);

        $response->assertSessionHasErrors('website');

        $this->assertDatabaseMissing('interessenten', [
            'mail' => 'spam@example.test',
        ]);

        Mail::assertNothingSent();
    }

    public function test_registration_is_rejected_when_submitted_too_fast()
    {
        Mail::fake();

        $response = $this->post('/registrieren', [
            'anrede' => 'Frau',
            'vorname' => 'Fast',
            'nachname' => 'Bot',
            'mail' => 'fast@example.test',
            'website' => '',
            'form_rendered_at' => time(),
        ]);

        $response->assertSessionHasErrors();

        $this->assertDatabaseMissing('interessenten', [
            'mail' => 'fast@example.test',
        ]);
    }

    public function test_email_confirmation_marks_the_interessent_as_verified()
    {
        $interessent = Interessenten::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'maria@example.test',
            'registration_source' => 'self-service',
        ]);

        $this->assertFalse($interessent->hasVerifiedEmail());

        $url = URL::temporarySignedRoute(
            'registrierung.bestaetigen',
            now()->addHours(48),
            ['interessent' => $interessent->id]
        );

        $response = $this->get($url);

        $response->assertOk();
        $this->assertTrue($interessent->fresh()->hasVerifiedEmail());
    }

    public function test_email_confirmation_fails_with_invalid_signature()
    {
        $interessent = Interessenten::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'maria2@example.test',
            'registration_source' => 'self-service',
        ]);

        $response = $this->get("/registrieren/{$interessent->id}/bestaetigen");

        $response->assertForbidden();
        $this->assertFalse($interessent->fresh()->hasVerifiedEmail());
    }
}
