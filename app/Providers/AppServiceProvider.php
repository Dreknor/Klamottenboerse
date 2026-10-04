<?php

namespace App\Providers;

use App\Domain\Kommunikation\VorlagenMail;
use App\Models\Boerse;
use App\Models\Posteingang;
use App\Support\BoerseKontext;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Model;
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

        // Admins dürfen alles.
        Gate::before(fn ($person) => $person->hasRole('admin') ? true : null);

        // "Passwort vergessen"-Mail auf Deutsch im Layout der Börse
        ResetPassword::toMailUsing(function ($person, string $token) {
            $link = route('password.reset', ['token' => $token, 'email' => $person->email]);
            $text = "Hallo {$person->vorname},\n\nmit diesem Link legst du ein neues Passwort fest (gültig für 60 Minuten):\n\n"
                ."[Neues Passwort festlegen]({$link})\n\nWenn du das nicht angefordert hast, kannst du diese Mail ignorieren.";

            return (new VorlagenMail('Neues Passwort für die Klamottenbörse', $text))->to($person->email);
        });

        View::composer('components.layouts.admin', function ($view) {
            $view->with([
                'aktuelleBoerse' => app(BoerseKontext::class)->get(),
                'alleBoersen' => Boerse::query()->orderByDesc('verkaufstag')->get(['id', 'titel', 'verkaufstag']),
                'offenePost' => Posteingang::query()->offen()->whereNull('gelesen_at')->count(),
            ]);
        });
    }
}
