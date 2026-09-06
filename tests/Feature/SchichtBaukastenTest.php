<?php

namespace Tests\Feature;

use App\Jobs\SendSchichtErinnerungMailJob;
use App\Model\Appointment;
use App\Model\Helfer;
use App\Model\Klamottenboerse;
use App\Model\MailLog;
use App\Model\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SchichtBaukastenTest extends TestCase
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

    public function test_verwaltung_can_create_appointments_with_bereich()
    {
        $klamottenboerse = $this->makeKlamottenboerse();
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => bcrypt('secret'),
            'verwaltung' => 1,
        ]);

        $response = $this->actingAs($admin)->post('/helfertermine', [
            'beschreibung' => 'Tische aufbauen',
            'bereich' => 'Aufbau',
            'date_start' => now()->addDay()->format('Y-m-d H:i:s'),
            'date_end' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
            'anzahl' => 2,
        ]);

        $response->assertRedirect(route('helfertermine'));

        $this->assertDatabaseCount('appointments', 2);
        $this->assertDatabaseHas('appointments', [
            'klamottenboerse_id' => $klamottenboerse->id,
            'bereich' => 'Aufbau',
            'beschreibung' => 'Tische aufbauen',
        ]);
    }

    public function test_reminder_command_queues_mail_for_upcoming_assigned_shift()
    {
        Queue::fake();

        $klamottenboerse = $this->makeKlamottenboerse();

        $appointment = Appointment::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'beschreibung' => 'Kasse Vormittag',
            'bereich' => 'Boersendienst',
            'date_start' => now()->addHours(5),
            'date_end' => now()->addHours(8),
        ]);

        $helfer = Helfer::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'name' => 'Helfer Hans',
            'mail' => 'hans@example.test',
            'telefon' => '0123456',
            'bereich' => 'Helfer',
        ]);

        $appointment->helfer_id = $helfer->id;
        $appointment->save();

        $this->artisan('schicht:erinnerung-versenden')->assertExitCode(0);

        $appointment->refresh();
        $this->assertNotNull($appointment->erinnerung_versendet_at);

        $this->assertDatabaseHas('mail_logs', [
            'helfer_id' => $helfer->id,
            'typ' => 'schichtErinnerung',
            'email' => 'hans@example.test',
        ]);

        Queue::assertPushed(SendSchichtErinnerungMailJob::class);
    }

    public function test_reminder_command_skips_far_future_and_already_reminded_shifts()
    {
        Queue::fake();

        $klamottenboerse = $this->makeKlamottenboerse();

        $helfer = Helfer::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'name' => 'Helfer Far',
            'mail' => 'far@example.test',
            'telefon' => '0123456',
            'bereich' => 'Helfer',
        ]);

        // Zu weit in der Zukunft
        $farAppointment = Appointment::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'beschreibung' => 'Abbau',
            'bereich' => 'Abbau',
            'date_start' => now()->addDays(10),
            'date_end' => now()->addDays(10)->addHours(2),
            'helfer_id' => $helfer->id,
        ]);

        // Bereits erinnert
        $alreadyReminded = Appointment::create([
            'klamottenboerse_id' => $klamottenboerse->id,
            'beschreibung' => 'Aufbau',
            'bereich' => 'Aufbau',
            'date_start' => now()->addHours(3),
            'date_end' => now()->addHours(5),
            'helfer_id' => $helfer->id,
            'erinnerung_versendet_at' => now()->subHour(),
        ]);

        $this->artisan('schicht:erinnerung-versenden')->assertExitCode(0);

        $this->assertDatabaseMissing('mail_logs', ['helfer_id' => $helfer->id]);
        Queue::assertNotPushed(SendSchichtErinnerungMailJob::class);
    }
}
