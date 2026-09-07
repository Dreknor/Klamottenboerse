<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verkäuferplatz frei geworden</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="color:#0f172a;">Ein Verkäuferplatz ist frei geworden!</h2>

        <p style="color:#334155;line-height:1.6;">
            du stehst auf unserer Warteliste und ein Platz (VK-Nummer
            {{ $vknummer->vknummer }}) ist nun für dich reserviert. Bitte
            bestätige deine Teilnahme innerhalb der nächsten
            {{ $warteliste->angebot_ablauf_at->diffForHumans($warteliste->angebot_versendet_at, true) }}
            über den folgenden Link. Reagierst du nicht rechtzeitig, geben
            wir den Platz automatisch an die nächste Person auf der
            Warteliste weiter.
        </p>

        <p style="text-align:center;margin:32px 0;">
            <a href="{{ $confirmationUrl }}"
               style="background:#16a34a;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:bold;">
                Platz bestätigen
            </a>
        </p>

        <p style="color:#64748b;font-size:13px;">
            Gültig bis: {{ $warteliste->angebot_ablauf_at->format('d.m.Y H:i') }} Uhr.
        </p>
    </div>
</body>
</html>
