# Klamottenbörse V2

Verwaltung, Kasse und öffentliche Seiten der Klamottenbörse des Ev. Kinderhauses Radebeul in einer Anwendung.
Laravel 13, Blade, Tailwind CSS 4, Alpine.js. Konzept: siehe „Klamottenbörse V2 – Konzept“.

## Bereiche

| Bereich | Adresse | Wer |
| --- | --- | --- |
| Öffentliche Seiten, Anmeldung, Helferliste | `/`, `/anmeldung`, `/helfen` | alle |
| Verkäuferportal (Magic-Link per Mail) | `/portal` | Verkäufer, Helfer |
| Orga-Backend | `/admin` | Rollen `orga`, `admin` (Login mit Passwort) |
| Kasse (offlinefähig) | `/kasse` | Rolle `kasse` |
| Tablet: Annahme, Rückpacken, Ausgabe | `/tablet` | Rolle `annahme` |

Ein Link aus einer Mail öffnet nur das Portal – Backend, Kasse und Tablet brauchen immer das Passwort.

## Einrichtung

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
cp .env.example .env && php artisan key:generate   # DB, MAIL_* und IMAP_* eintragen
php artisan migrate --force
php artisan db:seed --class=GrunddatenSeeder --force
php artisan db:seed --class=MailvorlagenSeeder --force
php artisan db:seed --class=SeitenSeeder --force      # Startseite, Infoseiten, Impressum, Datenschutz
php artisan storage:link
php artisan admin:anlegen vorname@example.org
```

Danach im Backend unter „Einstellungen“ die Betreiber-Angaben für Impressum und Datenschutz eintragen.

Für alle automatischen Abläufe (Mailversand mit Stundenlimit, Mailplan, Warteliste, Postfach-Abruf,
Aufgaben-Erinnerungen) genügt ein Cron-Eintrag:

```
* * * * * cd /pfad/zur/app && php artisan schedule:run >> /dev/null 2>&1
```

Automatisch laufen außerdem: Löschung inaktiver Personen nach 24 Monaten (mit Vorwarnung per Mail)
und die Erledigung selbst beantragter Löschungen nach der Abrechnung (`datenschutz:inaktive`).

## Übernahme aus V1

Zugangsdaten der alten Datenbank als `V1_DB_*` in die `.env` eintragen, dann:

```bash
php artisan v1:import --probe    # prüft alles, speichert nichts
php artisan v1:import            # echter Import (in eine leere V2-Datenbank)
php artisan v1:import --frisch   # erneuter Import: leert vorher alle Fachdaten
```

Am Ende werden je Börse Anzahl verkaufter Artikel und Umsatz zwischen V1 und V2 auf den Cent verglichen.
Bei einer Abweichung wird nichts gespeichert. Die Passwörter des Teams werden übernommen.

## Übernahme aus der Nextcloud

Bilder und Protokolle werden einmalig per WebDAV übernommen (wiederholbar, ohne Doppelungen).
Passwort am besten als App-Passwort in `NEXTCLOUD_PASSWORT` oder bei der Abfrage eingeben:

```bash
php artisan nextcloud:import --url=https://cloud.example.org/remote.php/dav/files/BENUTZER --benutzer=BENUTZER --bilder="Klamottenbörse/Fotos" --protokolle="Klamottenbörse/Protokolle"
```

## Lokal testen mit Mailpit

`MAIL_HOST=127.0.0.1`, `MAIL_PORT=1025` und `IMAP_TREIBER=mailpit` (Postfach-Adresse in `IMAP_USERNAME`).
Der Posteingang liest dann Mails an diese Adresse über die Mailpit-API statt über IMAP.

## Entwicklung

```bash
php artisan migrate:fresh --seed   # mit APP_ENV=local inkl. Beispieldaten (DemoSeeder)
composer run dev
vendor/bin/pest
vendor/bin/pint
```

Aufbau: Fachlogik in `app/Domain/<Bereich>` (eine Klasse je Vorgang), Models in `app/Models`,
Beträge immer als ganze Cent (`App\Support\Geld`).

## Push-Nachrichten

- Personen schalten Push selbst ein: im Verkäuferportal bzw. unter „Mein Konto“ (Team).
- Funktioniert nur über **HTTPS**. Auf dem iPhone muss die Seite vorher „Zum Home-Bildschirm“ hinzugefügt werden.
- Die Schlüssel (VAPID) werden beim ersten Aufruf automatisch erzeugt und in den Einstellungen gespeichert.
- Welche Mails zusätzlich als Push rausgehen, steht je Mailvorlage („Zusätzlich als Push-Nachricht“).
- Versand: `php artisan push:versenden` läuft jede Minute über den Scheduler.

## Fehlerprotokoll und Updates ohne SSH

- Warnungen und Fehler landen zusätzlich in der Datenbank: Backend → System & Fehler → Fehlerprotokoll.
- Updates: Backend → System & Fehler → „Nach Updates suchen“ / „Update jetzt installieren“
  (Wartungsmodus, `git pull --ff-only`, `composer install` nur bei geänderter composer.lock, `migrate`, Caches leeren).
  Die gebauten Assets (`public/build`) liegen im Repository – auf dem Server wird kein Node gebraucht.
  Vor dem Push also immer `npm run build` ausführen.
- Für Hoster mit anderem PHP-/Composer-Aufruf: `UPDATE_PHP`, `UPDATE_COMPOSER`, `UPDATE_BRANCH` in der `.env`.

## Demo zum Ausprobieren (für das Team)

Eine eigene Installation – z. B. `demo.klamottenboerse.de` – mit **eigener Datenbank und eigenem Verzeichnis**,
niemals die echte Installation umschalten.

1. Code wie üblich auschecken, `composer install`, `.env` anlegen (eigene Datenbank!) und zusätzlich:
   ```
   DEMO_MODUS=true
   DEMO_ZURUECKSETZEN_UM=03:15
   ```
   Mailversand (SMTP) wie in der echten Installation einrichten – Mails sollen ja ankommen.
2. Einmalig füllen: `php artisan migrate --force && php artisan demo:zuruecksetzen`
3. Cron wie üblich (`schedule:run` jede Minute) – setzt die Demo jede Nacht zurück.

Was die Demo macht:

- Anmeldeseite mit Knöpfen „als Admin / Orga-Team / Kasse / Annahme / Verkäuferin“ – kein Passwort nötig.
- Gelbes Band oben: Demo, Daten erfunden, nächtliches Zurücksetzen.
- Alle Beispielpersonen haben Adressen auf `example.org/.com/.net` – an sie geht **nie** eine Mail.
  Wer sich selbst mit echter Adresse anmeldet (als Verkäufer, Helfer, ins Team eingeladen), bekommt die Mails
  wirklich – mit „[DEMO]“ im Betreff. Push-Nachrichten funktionieren ebenfalls.
- Abgeschaltet: Software-Updates, Import aus V1/Nextcloud, Abruf des echten IMAP-Postfachs.
- Für Suchmaschinen gesperrt (noindex).
- Admins können unter System & Fehler jederzeit „Demo jetzt zurücksetzen“.

Lokal lässt sich die Demo getrennt von den Entwicklungsdaten starten (eigene Datenbank und eigener Storage-Ordner):
```
DB_DATABASE=klamottenboerse_demo DEMO_MODUS=true LARAVEL_STORAGE_PATH=$PWD/storage-demo php artisan serve --port=8078
```
