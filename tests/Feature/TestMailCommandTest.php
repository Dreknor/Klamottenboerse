<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TestMailCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_test_mail_to_given_address()
    {
        Mail::fake();

        $this->artisan('mail:test test@example.test')
            ->expectsOutput('Testmail wurde an test@example.test versendet.')
            ->assertExitCode(0);

        Mail::assertSent(\App\Mail\Mail::class, function ($mail) {
            return $mail->build()->hasTo('test@example.test')
                && $mail->subject === 'Testmail Klamottenboerse';
        });
    }

    public function test_command_fails_for_invalid_email_address()
    {
        Mail::fake();

        $this->artisan('mail:test invalid-email')
            ->expectsOutput('Ungueltige E-Mail-Adresse.')
            ->assertExitCode(1);

        Mail::assertNothingSent();
    }
}
