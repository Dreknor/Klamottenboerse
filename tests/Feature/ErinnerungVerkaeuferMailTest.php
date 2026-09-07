<?php

namespace Tests\Feature;

use App\Http\Controllers\MailController;
use App\Jobs\SendErinnerungVerkaeuferMailJob;
use App\Model\Interessenten;
use App\Model\Klamottenboerse;
use App\Model\Mailvorlagen;
use App\Model\VKnummer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ErinnerungVerkaeuferMailTest extends TestCase
{
    use RefreshDatabase;

    private function makeKlamottenboerse(int $sendErinnerungTage): Klamottenboerse
    {
        return Klamottenboerse::create([
            'datum' => Carbon::now()->addDays($sendErinnerungTage)->toDateString(),
            'anmeldung' => Carbon::now()->toDateString(),
            'anmeldungKinderhaus' => Carbon::now()->toDateString(),
            'anlieferung_von' => '08:00:00',
            'anlieferung_bis' => '10:00:00',
            'abholung_von' => '18:00:00',
            'abholung_bis' => '19:00:00',
            'maxTeile' => 100,
            'sendInvitation' => false,
            'sendErinnerung' => $sendErinnerungTage,
            'ort' => 'Turnhalle',
            'adresse' => 'Musterstraße 1',
        ]);
    }

    public function test_it_queues_reminder_mails_for_each_active_seller_exactly_once()
    {
        Queue::fake();

        Mailvorlagen::create([
            'name' => 'erinnerungVerkaeufer',
            'betreff' => 'Erinnerung',
            'text' => 'Hallo VORNAME',
            'html' => '<p>Hallo VORNAME</p>',
        ]);

        $klamottenboerse = $this->makeKlamottenboerse(3);

        $interessent = Interessenten::create([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Frau',
            'vorname' => 'Maria',
            'nachname' => 'Muster',
            'mail' => 'verkaeufer@example.test',
        ]);

        VKnummer::create([
            'vknummer' => 42,
            'klamottenboersen_id' => $klamottenboerse->id,
            'vergeben_an' => $interessent->id,
        ]);

        app(MailController::class)->erinnerungVerkaeufer();

        $this->assertDatabaseHas('mail_logs', [
            'email' => 'verkaeufer@example.test',
            'typ' => 'erinnerungVerkaeufer',
            'status' => 'queued',
        ]);

        Queue::assertPushed(SendErinnerungVerkaeuferMailJob::class, 1);

        // Erneuter Aufruf darf keine doppelte Mail für denselben Verkäufer einplanen.
        app(MailController::class)->erinnerungVerkaeufer();

        Queue::assertPushed(SendErinnerungVerkaeuferMailJob::class, 1);
        $this->assertEquals(1, \App\Model\MailLog::typ('erinnerungVerkaeufer')->count());
    }

    public function test_it_does_nothing_when_reminder_day_does_not_match()
    {
        Queue::fake();

        $this->makeKlamottenboerse(10);

        app(MailController::class)->erinnerungVerkaeufer();

        Queue::assertNotPushed(SendErinnerungVerkaeuferMailJob::class);
    }
}
