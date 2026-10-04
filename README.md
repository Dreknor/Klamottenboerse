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
php artisan admin:anlegen vorname@example.org
```

Für alle automatischen Abläufe (Mailversand mit Stundenlimit, Mailplan, Warteliste, Postfach-Abruf,
Aufgaben-Erinnerungen) genügt ein Cron-Eintrag:

```
* * * * * cd /pfad/zur/app && php artisan schedule:run >> /dev/null 2>&1
```

## Übernahme aus V1

Zugangsdaten der alten Datenbank als `V1_DB_*` in die `.env` eintragen, dann:

```bash
php artisan v1:import --probe    # prüft alles, speichert nichts
php artisan v1:import            # echter Import (in eine leere V2-Datenbank)
php artisan v1:import --frisch   # erneuter Import: leert vorher alle Fachdaten
```

Am Ende werden je Börse Anzahl verkaufter Artikel und Umsatz zwischen V1 und V2 auf den Cent verglichen.
Bei einer Abweichung wird nichts gespeichert. Die Passwörter des Teams werden übernommen.

## Entwicklung

```bash
php artisan migrate:fresh --seed   # mit APP_ENV=local inkl. Beispieldaten (DemoSeeder)
composer run dev
vendor/bin/pest
vendor/bin/pint
```

Aufbau: Fachlogik in `app/Domain/<Bereich>` (eine Klasse je Vorgang), Models in `app/Models`,
Beträge immer als ganze Cent (`App\Support\Geld`).
