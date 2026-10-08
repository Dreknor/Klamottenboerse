{{-- Eigenständig, ohne Datenbank und ohne gebaute Assets – muss auch funktionieren, wenn sonst nichts geht. --}}
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('titel')</title>
    <style>
        :root { --marke: #c2410c; --text: #292524; --leise: #57534e; --flaeche: #fafaf9; --karte: #fff; --rand: #e7e5e4; }
        @media (prefers-color-scheme: dark) { :root { --marke: #fb923c; --text: #f5f5f4; --leise: #a8a29e; --flaeche: #1c1917; --karte: #292524; --rand: #44403c; } }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 16px;
               font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; line-height: 1.5; color: var(--text); background: var(--flaeche); }
        main { max-width: 34rem; width: 100%; background: var(--karte); border: 1px solid var(--rand); border-radius: 12px; padding: 28px; }
        .code { font-size: .85rem; font-weight: 600; color: var(--marke); letter-spacing: .05em; text-transform: uppercase; margin: 0; }
        h1 { font-size: 1.5rem; margin: .25rem 0 .75rem; }
        p { margin: 0 0 .75rem; }
        .leise { color: var(--leise); font-size: .9rem; }
        .knoepfe { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 20px; }
        a.knopf { display: inline-block; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 500; border: 1px solid var(--rand); color: var(--text); }
        a.knopf.primaer { background: var(--marke); border-color: var(--marke); color: #fff; }
    </style>
</head>
<body>
<main>
    <p class="code">@yield('code')</p>
    <h1>@yield('titel')</h1>
    @yield('inhalt')
    <div class="knoepfe">
        <a class="knopf primaer" href="javascript:history.back()">Zurück</a>
        <a class="knopf" href="{{ url('/') }}">Zur Startseite</a>
    </div>
</main>
</body>
</html>
