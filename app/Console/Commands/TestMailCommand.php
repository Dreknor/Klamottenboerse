<?php

namespace App\Console\Commands;

use App\Mail\Mail as TestMailMailable;
use App\Model\Interessenten;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : Zieladresse fuer die Testmail}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Versendet eine einfache Testmail an eine angegebene E-Mail-Adresse.';

    public function handle(): int
    {
        $email = trim((string) $this->argument('email'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Ungueltige E-Mail-Adresse.');

            return self::FAILURE;
        }

        $interessent = new Interessenten([
            'uuid' => (string) Str::uuid(),
            'anrede' => 'Test',
            'vorname' => 'Mail',
            'nachname' => 'Empfang',
            'mail' => $email,
        ]);

        $request = \App\Http\Requests\MailRequest::create('/', 'POST', [
            'betreff' => 'Testmail Klamottenboerse',
            'text' => 'Dies ist eine Testmail aus dem Artisan-Command.',
            'html' => '<p>Dies ist eine Testmail aus dem Artisan-Command.</p>',
            'email' => $email,
        ]);

        Mail::to($email)->send(new TestMailMailable($request, $interessent));

        $this->info("Testmail wurde an {$email} versendet.");

        return self::SUCCESS;
    }
}
