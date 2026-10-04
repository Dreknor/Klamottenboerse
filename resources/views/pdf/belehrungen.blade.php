<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 12mm 15mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color: #000; }
    .blatt { height: 128mm; position: relative; overflow: hidden; }
    .schnitt { border-top: 1px dashed #777; margin: 4mm 0 6mm; height: 0; }
    .seitenumbruch { page-break-after: always; }
    table { width: 100%; border-collapse: collapse; }
    .kopf td { vertical-align: top; }
    .name { font-size: 14pt; font-weight: bold; }
    .klein { font-size: 9pt; color: #333; }
    .nummer { border: 1.5px solid #000; width: 38mm; text-align: center; padding: 2mm; }
    .nummer .zahl { font-size: 28pt; font-weight: bold; line-height: 1.1; }
    .text { margin-top: 4mm; line-height: 1.4; }
    .text p { margin: 0 0 2.5mm; }
    .unterschrift td { padding-top: 9mm; vertical-align: bottom; }
    .linie { border-bottom: 1px solid #000; height: 6mm; }
</style>
</head>
<body>
@php $annahme = ($boerse->anlieferung_beginn ?? $boerse->verkaufstag->copy()->subDay())->format('d.m.Y'); @endphp
@foreach ($teilnahmen as $t)
    <div class="blatt">
        <table class="kopf">
            <tr>
                <td>
                    <div class="name">{{ $t->person?->vorname }} {{ $t->person?->nachname }}</div>
                    <div>Telefon: {{ $t->person?->telefon ?: '________________________' }}</div>
                    <div class="klein">{{ $boerse->titel }} · {{ $boerse->verkaufstag->isoFormat('dddd, D. MMMM YYYY') }}</div>
                </td>
                <td class="nummer">
                    <div class="klein">Verkäufernummer</div>
                    <div class="zahl">{{ $t->nummer }}</div>
                </td>
            </tr>
        </table>

        <div class="text">{!! $texte[$t->id] !!}</div>

        <table class="unterschrift">
            <tr>
                <td style="width: 32%">Datum: {{ $annahme }}</td>
                <td style="width: 18%; text-align: right; padding-right: 2mm;">Unterschrift:</td>
                <td class="linie"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right; padding-right: 2mm;"><strong>Verkaufserlös erhalten:</strong></td>
                <td class="linie"></td>
            </tr>
        </table>
    </div>
    @if (! $loop->last)
        @if ($loop->iteration % 2 === 1)
            <div class="schnitt"></div>
        @else
            <div class="seitenumbruch"></div>
        @endif
    @endif
@endforeach
</body>
</html>
