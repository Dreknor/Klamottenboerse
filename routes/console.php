<?php

use App\Domain\Ablage\NextcloudImport;
use App\Domain\Kommunikation\ImapPostfach;
use App\Domain\Kommunikation\MailplanAusfuehren;
use App\Domain\Kommunikation\Postausgang;
use App\Domain\Orga\AufgabenErinnern;
use App\Domain\Personen\InaktiveBereinigen;
use App\Domain\Teilnahme\Actions\WartelisteNachruecken;
use App\Models\Boerse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Alle automatischen Abläufe. Auf dem Server genügt ein Cron-Eintrag:
|   * * * * * cd /pfad/zur/app && php artisan schedule:run >> /dev/null 2>&1
*/

Artisan::command('mails:versenden', function () {
    $this->info(Postausgang::versendeFaellige().' Mail(s) versendet.');
})->purpose('Versendet wartende Mails im Rahmen des Stundenlimits');

Artisan::command('mailplan:ausfuehren', function () {
    $this->info((new MailplanAusfuehren)().' Mail(s) aus dem Mailplan eingeplant.');
})->purpose('Plant fällige Mails aus dem Mailplan aller Börsen ein');

Artisan::command('warteliste:nachruecken', function (WartelisteNachruecken $nachruecken) {
    $angebote = Boerse::query()->offen()->where('verkaufstag', '>=', today())->get()
        ->sum(fn (Boerse $boerse) => $nachruecken($boerse));
    $this->info("{$angebote} Angebot(e) an die Warteliste verschickt.");
})->purpose('Beendet abgelaufene Angebote und bietet freie Plätze der Warteliste an');

Artisan::command('posteingang:abrufen', function () {
    if (! ImapPostfach::istKonfiguriert()) {
        $this->warn('IMAP ist nicht eingerichtet.');

        return;
    }
    $this->info((new ImapPostfach)->abrufen().' neue Mail(s) abgerufen.');
})->purpose('Ruft neue Mails aus dem IMAP-Postfach ab');

Artisan::command('aufgaben:erinnern', function () {
    $this->info((new AufgabenErinnern)().' Erinnerung(en) eingeplant.');
})->purpose('Erinnert Zuständige an fällige Aufgaben');

Artisan::command('datenschutz:inaktive', function (InaktiveBereinigen $bereinigen) {
    $ergebnis = $bereinigen();
    $this->info("{$ergebnis['angeschrieben']} Person(en) angeschrieben, {$ergebnis['geloescht']} gelöscht.");
})->purpose('Schreibt seit 24 Monaten inaktive Personen an und löscht sie nach Ablauf der Frist');

Artisan::command('nextcloud:import {--url= : WebDAV-Adresse, z. B. https://cloud.example.org/remote.php/dav/files/benutzer} {--benutzer=} {--bilder= : Ordner mit Bildern} {--protokolle= : Ordner mit Protokollen}', function () {
    $passwort = env('NEXTCLOUD_PASSWORT') ?: $this->secret('Nextcloud-Passwort (am besten ein App-Passwort)');
    $import = new NextcloudImport((string) $this->option('url'), (string) $this->option('benutzer'), (string) $passwort);

    if ($this->option('bilder')) {
        $import->bilder($this->option('bilder'));
    }
    if ($this->option('protokolle')) {
        $import->protokolle($this->option('protokolle'));
    }

    $z = $import->zaehler;
    $this->info("{$z['bilder']} Bilder, {$z['protokolle']} Protokolle (Text), {$z['dateien']} Protokoll-Dateien übernommen; {$z['uebersprungen']} bereits vorhanden.");
})->purpose('Übernimmt einmalig Bilder und Protokolle aus der Nextcloud (WebDAV)');

Schedule::command('mails:versenden')->everyMinute()->withoutOverlapping();
Schedule::command('mailplan:ausfuehren')->everyTenMinutes()->withoutOverlapping();
Schedule::command('warteliste:nachruecken')->everyFifteenMinutes()->withoutOverlapping();
Schedule::command('posteingang:abrufen')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('aufgaben:erinnern')->dailyAt('07:00');
Schedule::command('datenschutz:inaktive')->dailyAt('03:00');
