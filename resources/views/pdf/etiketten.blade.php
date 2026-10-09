<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
@php
    [$spalten, $reihen, $breite, $hoehe] = $vorlage->raster();
    $g = $vorlage->gestaltung();
    $positionen = $vorlage->positionen();
@endphp
<style>
    /* {{ $vorlage->label() }} – auch auf Normalpapier zum Ausschneiden (feine Schnittlinien) */
    @page { margin: 0; size: 210mm 297mm; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; color: #000; }
    .bogen { position: relative; width: 210mm; height: 296mm; page-break-after: always; overflow: hidden; }
    .bogen.letzter { page-break-after: auto; }
    .etikett { position: absolute; width: {{ $breite }}mm; height: {{ $hoehe }}mm; overflow: hidden; border: 0.1mm dashed #bbb; }
    .innen { position: absolute; left: {{ $g['rand'] }}mm; top: {{ $g['rand'] }}mm; right: {{ $g['rand'] }}mm; bottom: {{ $g['rand'] }}mm; }
    .qr { position: absolute; left: 0; top: 0; width: {{ $g['qr'] }}mm; height: {{ $g['qr'] }}mm; }
    .text { position: absolute; left: {{ $g['qr'] + $g['rand'] }}mm; top: 0; right: 0; bottom: 0; overflow: hidden; }
    .kopf { font-size: {{ $g['kopf'] }}pt; font-weight: bold; line-height: 1.1; white-space: nowrap; }
    .preis { font-size: {{ $g['preis'] }}pt; font-weight: bold; line-height: 1.1; margin-top: 1mm; white-space: nowrap; }
    .beschreibung { font-size: {{ $g['text'] }}pt; line-height: 1.2; margin-top: 1mm; height: {{ $g['textHoehe'] }}mm; overflow: hidden; }
</style>
</head>
<body>
@foreach ($seiten as $seite)
    <div class="bogen @if ($loop->last) letzter @endif">
        @foreach ($seite->values() as $i => $e)
            @continue($e === null)
            <div class="etikett" style="left: {{ $positionen[$i][0] }}mm; top: {{ $positionen[$i][1] }}mm;">
                <div class="innen">
                    <img class="qr" src="{{ $e['qr'] }}" alt="">
                    <div class="text">
                        <div class="kopf">{{ $e['nummer'] }}-{{ $e['laufnummer'] }}</div>
                        <div class="preis">{{ $e['preis'] }}</div>
                        @if ($g['textHoehe'] >= 2)
                            <div class="beschreibung">{{ $e['beschreibung'] }}@if ($e['groesse']) · Gr. {{ $e['groesse'] }}@endif</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endforeach
</body>
</html>
