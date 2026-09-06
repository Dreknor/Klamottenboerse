<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Erinnerung an deine Helferschicht</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f1f5f9; padding:24px;">
    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;padding:32px;">
        <h2 style="color:#0f172a;">Hallo {{ $helfer->name }},</h2>

        <p style="color:#334155;line-height:1.6;">
            wir möchten dich an deine bevorstehende Helferschicht
            "{{ $appointment->beschreibung }}" ({{ \App\Model\Appointment::BEREICHE[$appointment->bereich] ?? $appointment->bereich }})
            erinnern:
        </p>

        <p style="background:#f8fafc;border-radius:8px;padding:16px;color:#0f172a;">
            <strong>Beginn:</strong> {{ $appointment->date_start->format('d.m.Y H:i') }} Uhr<br>
            <strong>Ende:</strong> {{ $appointment->date_end->format('H:i') }} Uhr
        </p>

        <p style="color:#334155;line-height:1.6;">
            Vielen Dank für deine Unterstützung!
        </p>
    </div>
</body>
</html>
