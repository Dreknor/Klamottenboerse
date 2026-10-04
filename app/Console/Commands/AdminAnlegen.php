<?php

namespace App\Console\Commands;

use App\Models\Person;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/** Legt einen Admin-Zugang an oder macht eine bestehende Person zum Admin (Ersteinrichtung). */
class AdminAnlegen extends Command
{
    protected $signature = 'admin:anlegen {email} {--vorname=Admin} {--nachname=Team} {--passwort= : Ohne Angabe wird eines erzeugt}';

    protected $description = 'Legt einen Admin-Zugang an bzw. setzt ein neues Passwort';

    public function handle(): int
    {
        $passwort = $this->option('passwort') ?: Str::password(16, symbols: false);

        $person = Person::query()->firstOrCreate(
            ['email' => Str::lower($this->argument('email'))],
            ['vorname' => $this->option('vorname'), 'nachname' => $this->option('nachname'), 'email_verified_at' => now()],
        );
        $person->update(['password' => $passwort]);
        $person->assignRole(['admin', 'orga']);

        $this->info("Admin-Zugang für {$person->email} ist eingerichtet.");
        if (! $this->option('passwort')) {
            $this->line("Passwort (bitte nach dem ersten Login ändern lassen): {$passwort}");
        }

        return self::SUCCESS;
    }
}
