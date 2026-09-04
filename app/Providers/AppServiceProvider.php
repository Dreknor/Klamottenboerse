<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /** Maximale Mails pro Stunde (Puffer unter dem Hoster-Limit von 60) */
    const MAILS_PRO_STUNDE = 55;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        // Begrenzt den Versand aller darüber laufenden Queue-Jobs auf max.
        // MAILS_PRO_STUNDE E-Mails pro Stunde, unabhängig davon, wann/wie oft
        // der Queue-Worker läuft. Jobs, die das Limit überschreiten, werden
        // automatisch zurückgestellt und später erneut versucht.
        RateLimiter::for('mails', function () {
            return Limit::perHour(self::MAILS_PRO_STUNDE);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
