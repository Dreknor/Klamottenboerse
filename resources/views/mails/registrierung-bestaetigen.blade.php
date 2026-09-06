<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Registrierung bestätigen</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="color:#0f172a;">Hallo {{ $interessent->vorname }},</h2>

        <p style="color:#334155;line-height:1.6;">
            vielen Dank für deine Registrierung bei der Klamottenbörse.
            Bitte bestätige deine E-Mail-Adresse, damit wir deine Anmeldung
            bearbeiten können.
        </p>

        <p style="text-align:center;margin:32px 0;">
            <a href="{{ $verificationUrl }}"
               style="background:#0284c7;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">
                E-Mail-Adresse bestätigen
            </a>
        </p>

        <p style="color:#64748b;font-size:13px;">
            Falls der Button nicht funktioniert, kopiere folgenden Link in
            deinen Browser:<br>
            <a href="{{ $verificationUrl }}">{{ $verificationUrl }}</a>
        </p>

        <p style="color:#64748b;font-size:13px;">
            Dieser Link ist aus Sicherheitsgründen zeitlich begrenzt gültig.
        </p>
    </div>
</body>
</html>
