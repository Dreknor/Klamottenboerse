<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    /* A4, 3 × 8 Etiketten à 70 × 37 mm (gängige Bögen, z. B. 3475) – auch auf Normalpapier zum Ausschneiden */
    @page { margin: 0; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #000; }
    table.bogen { border-collapse: collapse; table-layout: fixed; margin: 0; page-break-after: always; }
    table.bogen:last-child { page-break-after: auto; }
    td.etikett { width: 70mm; height: 37mm; padding: 2.5mm 3mm; vertical-align: top; overflow: hidden; border: 0.1mm dashed #bbb; }
    .qr { width: 27mm; height: 27mm; }
    .text { padding-left: 2mm; vertical-align: top; }
    .kopf { font-size: 16pt; font-weight: bold; line-height: 1.1; }
    .preis { font-size: 16pt; font-weight: bold; margin-top: 1.5mm; }
    .beschreibung { font-size: 7.5pt; margin-top: 1.5mm; height: 9mm; overflow: hidden; }
</style>
</head>
<body>
@foreach ($etiketten->chunk(24) as $seite)
    <table class="bogen">
        @foreach ($seite->chunk(3) as $reihe)
            <tr>
                @foreach ($reihe as $e)
                    <td class="etikett">
                        <table><tr>
                            <td><img class="qr" src="{{ $e['qr'] }}" alt=""></td>
                            <td class="text">
                                <div class="kopf">{{ $e['nummer'] }}-{{ $e['laufnummer'] }}</div>
                                <div class="preis">{{ $e['preis'] }}</div>
                                <div class="beschreibung">{{ $e['beschreibung'] }}@if ($e['groesse']) · Gr. {{ $e['groesse'] }}@endif</div>
                            </td>
                        </tr></table>
                    </td>
                @endforeach
                @for ($i = $reihe->count(); $i < 3; $i++)<td class="etikett"></td>@endfor
            </tr>
        @endforeach
    </table>
@endforeach
</body>
</html>
