<?php

use App\Http\Middleware\NichtInDemo;
use App\Http\Middleware\NurMitPasswort;
use App\Http\Middleware\NurOrga;
use App\Http\Middleware\NurTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'orga' => NurOrga::class,
            'team' => NurTeam::class,
            'nicht-in-demo' => NichtInDemo::class,
            'passwort' => NurMitPasswort::class,
        ]);
        $middleware->redirectGuestsTo(fn (Request $request) => $request->is('portal*') ? route('portal.link') : route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
