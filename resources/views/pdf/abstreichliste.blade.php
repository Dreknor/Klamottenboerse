<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 12mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; color: #000; }
    h1 { font-size: 14pt; margin: 0 0 1mm; }
    .unter { color: #444; margin-bottom: 4mm; font-size: 9pt; }
    table.spalten { width: 100%; border-collapse: collapse; }
    table.spalten > tbody > tr > td { vertical-align: top; padding: 0 2mm; }
    .kopf { font-weight: bold; border-bottom: 1.5px solid #000; padding-bottom: 1mm; margin-bottom: 1mm; }
    .zeile { height: 6.2mm; line-height: 6.2mm; border-bottom: 1px solid #ccc; }
    .kasten { display: inline-block; width: 3.6mm; height: 3.6mm; border: 1px solid #000; margin-right: 1.5mm; vertical-align: middle; }
    .klein { font-size: 8pt; color: #444; }
</style>
</head>
<body>
<h1>Abstreichliste</h1>
<div class="unter">{{ $boerse->titel }} · {{ $boerse->verkaufstag->isoFormat('D. MMMM YYYY') }} · Kästchen: abgegeben / abgeholt</div>
<table class="spalten">
    <tbody>
    <tr>
        @foreach ($bloecke as $start => $teilnahmen)
            <td style="width: {{ floor(100 / max(1, count($bloecke))) }}%">
                <div class="kopf">{{ $start }}er <span class="klein">({{ $teilnahmen->count() }})</span></div>
                @foreach ($teilnahmen as $t)
                    <div class="zeile"><span class="kasten"></span><span class="kasten"></span><strong>{{ $t->nummer }}</strong>@if ($t->ist_kinderhaus) <span class="klein">KH</span>@endif</div>
                @endforeach
            </td>
        @endforeach
    </tr>
    </tbody>
</table>
</body>
</html>
