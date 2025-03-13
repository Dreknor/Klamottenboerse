<!DOCTYPE html>
<html>
<head>
    <title>Klamottenbörse - Ihre Verkaufsübersicht</title>
</head>
<body>
<h1>Klamottenbörse - Ihre Verkaufsübersicht</h1>
<p>Hallo {{$vorname}} {{$nachname}},</p>
<p>Über den folgenden Link können Sie Ihre Verkaufsübersicht abrufen:</p>
<p>
    <a href="{{route('ergebnis.show', $uuid)}}">{{route('ergebnis.show', $uuid)}}</a>
</p>

<p>Vielen Dank für Ihre Teilnahme an der Klamottenbörse.</p>

<p>Herzliche Grüße</p>
<p>Ihr Klamottenbörse-Team</p>
</body>
</html>
