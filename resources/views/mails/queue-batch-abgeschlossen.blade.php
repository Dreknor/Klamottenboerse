<!doctype html>
<html lang="de">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue-Batch Statusbericht</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif; }
        .wrapper { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 6px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .header { background-color: {{ $batchNummer === $batchAnzahl ? '#28a745' : '#007bff' }}; padding: 28px 32px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 20px; }
        .body { padding: 32px; color: #333333; font-size: 15px; line-height: 1.6; }
        .stat-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .stat-table td { padding: 10px 14px; border-bottom: 1px solid #eeeeee; }
        .stat-table td:first-child { font-weight: bold; color: #555; width: 55%; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 13px; font-weight: bold;
                 background-color: {{ $batchNummer === $batchAnzahl ? '#d4edda' : '#cce5ff' }};
                 color: {{ $batchNummer === $batchAnzahl ? '#155724' : '#004085' }}; }
        .footer { background-color: #f8f8f8; padding: 16px 32px; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; }
        .progress-bar-wrap { background: #e9ecef; border-radius: 8px; height: 12px; overflow: hidden; margin: 6px 0 4px; }
        .progress-bar-fill { height: 100%; border-radius: 8px; background-color: {{ $batchNummer === $batchAnzahl ? '#28a745' : '#007bff' }};
                             width: {{ round(($batchNummer / $batchAnzahl) * 100) }}%; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="header">
        <h1>
            @if($batchNummer === $batchAnzahl)
                ✓ Versand vollständig abgeschlossen
            @else
                📬 Batch {{ $batchNummer }} von {{ $batchAnzahl }} abgeschlossen
            @endif
        </h1>
    </div>
    <div class="body">
        <p>Hallo,</p>
        <p>
            @if($batchNummer === $batchAnzahl)
                alle Anmeldungs-Einladungsmails für die <strong>{{ $boerseName }}</strong> wurden erfolgreich versandt.
            @else
                der <strong>{{ $batchNummer }}. Batch</strong> der Anmeldungs-Einladungsmails für die
                <strong>{{ $boerseName }}</strong> wurde soeben verarbeitet.
                Der nächste Batch wird in ca. 60 Minuten versendet.
            @endif
        </p>

        <table class="stat-table">
            <tr>
                <td>Status</td>
                <td><span class="badge">{{ $batchNummer === $batchAnzahl ? 'Abgeschlossen' : 'In Bearbeitung' }}</span></td>
            </tr>
            <tr>
                <td>Batch</td>
                <td>{{ $batchNummer }} / {{ $batchAnzahl }}</td>
            </tr>
            <tr>
                <td>Mails in diesem Batch</td>
                <td>{{ $mailsInBatch }}</td>
            </tr>
            <tr>
                <td>Mails insgesamt</td>
                <td>{{ $mailsGesamt }}</td>
            </tr>
            <tr>
                <td>Bisher versandt</td>
                <td>{{ $batchNummer * $mailsInBatch > $mailsGesamt ? $mailsGesamt : min($batchNummer * 55, $mailsGesamt) }} von {{ $mailsGesamt }}</td>
            </tr>
            <tr>
                <td>Klamottenbörse</td>
                <td>{{ $boerseName }}</td>
            </tr>
        </table>

        <p style="font-size:13px; color:#666;">Fortschritt:</p>
        <div class="progress-bar-wrap">
            <div class="progress-bar-fill"></div>
        </div>
        <p style="font-size:12px; color:#999; text-align:right; margin-top:2px;">
            {{ round(($batchNummer / $batchAnzahl) * 100) }}%
        </p>

        @if($batchNummer === $batchAnzahl)
            <p>Es sind keine weiteren automatisierten Versand-Aktionen geplant.</p>
        @endif

        <p style="margin-top: 24px; color: #888; font-size: 13px;">
            Diese Nachricht wurde automatisch generiert.
        </p>
    </div>
    <div class="footer">
        Klamottenbörse · Automatischer Systembericht
    </div>
</div>
</body>
</html>

