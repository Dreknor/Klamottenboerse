<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Löschung bestätigen</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="color:#0f172a;">Hallo {{ $interessent->vorname }},</h2>

        <p style="color:#334155;line-height:1.6;">
            du hast die Löschung deiner Registrierung bei der Klamottenbörse
            angefragt. Bitte bestätige diesen Wunsch über den folgenden Link.
            Deine Daten werden zunächst gesperrt (Soft-Delete) und nach einer
            Karenzzeit von 30 Tagen endgültig gelöscht. In dieser Zeit kannst
            du dich an unser Team wenden, falls du es dir anders überlegst.
        </p>

        <p style="text-align:center;margin:32px 0;">
            <a href="{{ $confirmationUrl }}"
               style="background:#dc2626;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">
                Löschung bestätigen
            </a>
        </p>

        <p style="color:#64748b;font-size:13px;">
            Falls du diese Löschung nicht angefragt hast, kannst du diese
            E-Mail einfach ignorieren.
        </p>
    </div>
</body>
</html>
