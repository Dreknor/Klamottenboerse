<?php

namespace App\Providers;

use App\Domain\Kommunikation\VorlagenMail;
use App\Models\Boerse;
use App\Models\Fehler;
use App\Models\Posteingang;
use App\Models\Seite;
use App\Support\BoerseKontext;
use App\Support\Demo;
use App\Support\Grunddaten;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\NoPendingMigrations;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(BoerseKontext::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
        Carbon::setLocale('de');

        // Nach jedem „migrate“ (Installation, Online-Update) fehlende Rollen und Standardvorlagen ergänzen
        Event::listen([MigrationsEnded::class, NoPendingMigrations::class], function (MigrationsEnded|NoPendingMigrations $ereignis) {
            if ($ereignis->method === 'up') {
                Grunddaten::sicherstellen();
            }
        });

        // Demo: echte Mails an die Adressen der Testenden – mit [DEMO] im Betreff.
        // Die erfundenen Beispielpersonen (example.org usw.) bekommen nie etwas.
        Event::listen(MessageSending::class, function (MessageSending $ereignis) {
            if (! Demo::aktiv()) {
                return;
            }
            $empfaenger = array_map(fn ($a) => $a->getAddress(), $ereignis->message->getTo());
            if ($empfaenger === [] || collect($empfaenger)->every(fn ($a) => Demo::istBeispieladresse($a))) {
                return false;
            }
            $ereignis->message->subject('[DEMO] '.$ereignis->message->getSubject());
        });

        // Admins dürfen alles.
        Gate::before(fn ($person) => $person->hasRole('admin') ? true : null);

        // "Passwort vergessen"-Mail bzw. Einladung ins Team – auf Deutsch im Layout der Börse
        ResetPassword::toMailUsing(function ($person, string $token) {
            $link = route('password.reset', ['token' => $token, 'email' => $person->email]);
            $stunden = intdiv((int) config('auth.passwords.users.expire'), 60);

            if ($person->password === null) {
                $text = "Hallo {$person->vorname},\n\ndu wurdest ins Team der Klamottenbörse aufgenommen. "
                    ."Mit diesem Link legst du dein Passwort fest (gültig für {$stunden} Stunden) und kannst dich danach anmelden:\n\n"
                    ."[Passwort festlegen]({$link})";

                return (new VorlagenMail('Dein Zugang zur Klamottenbörse', $text))->to($person->email);
            }

            $text = "Hallo {$person->vorname},\n\nmit diesem Link legst du ein neues Passwort fest (gültig für {$stunden} Stunden):\n\n"
                ."[Neues Passwort festlegen]({$link})\n\nWenn du das nicht angefordert hast, kannst du diese Mail ignorieren.";

            return (new VorlagenMail('Neues Passwort für die Klamottenbörse', $text))->to($person->email);
        });

        View::composer('components.layouts.oeffentlich', function ($view) {
            $view->with('menue', Seite::query()->veroeffentlicht()->where('im_menue', true)
                ->orderBy('menue_reihenfolge')->orderBy('titel')->get(['slug', 'titel']));
        });

        View::composer('components.layouts.admin', function ($view) {
            $view->with([
                'aktuelleBoerse' => app(BoerseKontext::class)->get(),
                'alleBoersen' => Boerse::query()->orderByDesc('verkaufstag')->get(['id', 'titel', 'verkaufstag']),
                'offenePost' => Posteingang::query()->offen()->whereNull('gelesen_at')->count(),
                // Fehler nur für Admins zählen – nur sie sehen das Fehlerprotokoll
                'offeneFehler' => auth()->user()?->hasRole('admin') ? Fehler::query()->whereNull('erledigt_at')->count() : 0,
            ]);
        });
    }
}
