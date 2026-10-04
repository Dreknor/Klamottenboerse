<?php

namespace App\Providers;

use App\Models\Boerse;
use App\Models\Posteingang;
use App\Support\BoerseKontext;
use Carbon\Carbon;
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

        View::composer('components.layouts.admin', function ($view) {
            $view->with([
                'aktuelleBoerse' => app(BoerseKontext::class)->get(),
                'alleBoersen' => Boerse::query()->orderByDesc('verkaufstag')->get(['id', 'titel', 'verkaufstag']),
                'offenePost' => Posteingang::query()->offen()->whereNull('gelesen_at')->count(),
            ]);
        });
    }
}
