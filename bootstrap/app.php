<?php

use App\Http\Middleware\NichtInDemo;
use App\Http\Middleware\NurMitPasswort;
use App\Http\Middleware\NurOrga;
use App\Http\Middleware\NurTeam;
use App\Support\Fehlermeldung;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        $ohnePasswort = fn (Request $request) => $request->except(['password', 'password_confirmation', 'aktuelles_passwort', '_token']);

        // Formular zu lange offen (Sitzung abgelaufen): zurück statt „419 Page Expired“
        $exceptions->render(function (TokenMismatchException $e, Request $request) use ($ohnePasswort) {
            $meldung = 'Die Seite war zu lange geöffnet. Bitte die Eingabe noch einmal absenden.';

            return $request->expectsJson()
                ? response()->json(['message' => $meldung], 419)
                : back()->withInput($ohnePasswort($request))->with('fehler', $meldung);
        });

        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $meldung = 'Die hochgeladene Datei ist zu groß. Erlaubt sind höchstens '.ini_get('upload_max_filesize').'B – bitte verkleinern (z. B. ein Foto mit geringerer Auflösung).';

            return $request->expectsJson() ? response()->json(['message' => $meldung], 413) : back()->with('fehler', $meldung);
        });

        // Alle übrigen unerwarteten Fehler: verständliche Meldung statt nackter „500 Server Error“.
        // Protokolliert werden sie trotzdem (Backend → System → Fehlerprotokoll).
        $exceptions->render(function (Throwable $e, Request $request) use ($ohnePasswort) {
            if (config('app.debug') || $e instanceof HttpExceptionInterface || $e instanceof HttpResponseException
                || $e instanceof ValidationException || $e instanceof AuthenticationException
                || $e instanceof AuthorizationException || $e instanceof ModelNotFoundException) {
                return null; // übernimmt Laravel (Fehlerseiten 403/404, Formularfehler, Login …)
            }

            $meldung = Fehlermeldung::fuer($e);
            if ($request->expectsJson()) {
                return response()->json(['message' => $meldung], 500);
            }

            // Abgeschickte Formulare: zurück zur Seite, mit den Eingaben – damit nichts verloren geht.
            // Ohne Datenbank klappt das nicht (Sitzung), dann direkt die Fehlerseite.
            $datenbankWeg = ($e instanceof QueryException || $e instanceof PDOException) && str_contains(strtolower($e->getMessage()), 'connect');
            if (! $request->isMethod('GET') && ! $datenbankWeg && url()->previous() !== $request->fullUrl()) {
                return back()->withInput($ohnePasswort($request))->with('fehler', $meldung);
            }

            return response()->view('errors.500', ['meldung' => $meldung], 500);
        });
    })->create();
