<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Anmeldungs-Mails gestaffelt versenden (max. 55/Stunde wegen Hoster-Beschränkung)
        $schedule->command('mail:anmeldung-moeglich')->dailyAt('07:00');

        // Erinnerungsmail an Verkäufer
        $schedule->call('App\Http\Controllers\MailController@erinnerungVerkaeufer')->dailyAt('00:02:00');

        // Queue-Worker: alle 5 Minuten ausstehende Jobs verarbeiten
        // (nutze den Cron-basierten Ansatz, falls kein dauerhafter Worker läuft)
        $schedule->command('queue:work --stop-when-empty --max-jobs=60')->everyFiveMinutes()->withoutOverlapping();

        // Endgültige Löschung selbst-gelöschter Interessenten nach Karenzzeit (Softdelete-Schutz)
        $schedule->command('interessenten:purge-deleted')->dailyAt('03:30');

        // Automatisches Wartelisten-Nachrücken: freie VK-Nummern anbieten
        $schedule->command('warteliste:nachruecken')->hourly()->withoutOverlapping();

        // Abgelaufene, unbestätigte Wartelisten-Angebote zurücksetzen
        $schedule->command('warteliste:angebote-bereinigen')->hourly()->withoutOverlapping();

        // Erinnerungs-Mails an Helfer vor ihrer Schicht (Aufbau/Börsendienst/Abbau)
        $schedule->command('schicht:erinnerung-versenden')->hourly()->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
