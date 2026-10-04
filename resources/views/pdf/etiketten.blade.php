<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    /* A4, 3 × 8 Etiketten à 70 × 37 mm (gängige Bögen, z. B. 3475) – auch auf Normalpapier zum Ausschneiden */
    @page { margin: 0; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #000; }
    table { border-collapse: collapse; table-layout: fixed; margin: 0; page-break-after: always; }
    table:last-child { page-break-after: auto; }
    td { width: 70mm; height: 37mm; padding: 2mm 3mm; vertical-align: top; overflow: hidden; border: 0.1mm dashed #bbb; }
    .kopf { font-size: 15pt; font-weight: bold; }
    .preis { float: right; font-size: 15pt; font-weight: bold; }
    .text { font-size: 8pt; height: 8mm; overflow: hidden; margin-top: 1mm; }
    .code { margin-top: 1mm; text-align: center; }
    .code img { height: 11mm; width: 60mm; }
    .klein { font-size: 6.5pt; text-align: center; }
</style>
</head>
<body>
@foreach ($etiketten->chunk(24) as $seite)
    <table>
        @foreach ($seite->chunk(3) as $reihe)
            <tr>
                @foreach ($reihe as $e)
                    <td>
                        <div><span class="preis">{{ $e['preis'] }}</span><span class="kopf">{{ $e['nummer'] }}-{{ $e['laufnummer'] }}</span></div>
                        <div class="text">{{ $e['beschreibung'] }}@if ($e['groesse']) · Gr. {{ $e['groesse'] }}@endif</div>
                        <div class="code"><img src="data:image/png;base64,{{ $e['barcode'] }}" alt=""></div>
                    </td>
                @endforeach
                @for ($i = $reihe->count(); $i < 3; $i++)<td></td>@endfor
            </tr>
        @endforeach
    </table>
@endforeach
</body>
</html>
