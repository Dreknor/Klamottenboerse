<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 14mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #000; }
    h1 { font-size: 15pt; margin: 0 0 1mm; }
    .unter { color: #444; margin-bottom: 5mm; }
    .schicht { margin-bottom: 5mm; page-break-inside: avoid; }
    .titel { font-weight: bold; font-size: 11pt; border-bottom: 1.5px solid #000; padding-bottom: 1mm; }
    .titel span { font-weight: normal; color: #444; }
    table { width: 100%; border-collapse: collapse; margin-top: 1mm; }
    td { border-bottom: 1px solid #ccc; padding: 1.2mm 2mm; }
    td.da { width: 14mm; }
    .kasten { display: inline-block; width: 3.6mm; height: 3.6mm; border: 1px solid #000; }
    .fehlt { color: #a00; }
</style>
</head>
<body>
<h1>Helferliste</h1>
<div class="unter">{{ $boerse->titel }} · {{ $boerse->verkaufstag->isoFormat('dddd, D. MMMM YYYY') }}</div>
@forelse ($schichten as $s)
    <div class="schicht">
        <div class="titel">
            {{ $s->bereich }} · {{ $s->beginn->isoFormat('dd D.M., H:mm') }}–{{ $s->ende->format('H:i') }} Uhr
            <span>({{ $s->einteilungen->count() }} von {{ $s->soll }})</span>
            @if ($s->einteilungen->count() < $s->soll)<span class="fehlt"> – es fehlen {{ $s->soll - $s->einteilungen->count() }}</span>@endif
        </div>
        @if ($s->beschreibung)<div style="color:#444; font-size: 9pt;">{{ $s->beschreibung }}</div>@endif
        <table>
            @foreach ($s->einteilungen as $e)
                <tr>
                    <td class="da"><span class="kasten"></span></td>
                    <td>{{ $e->person?->name }}</td>
                    <td>{{ $e->person?->telefon }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <p>Noch keine Schichten angelegt.</p>
@endforelse
</body>
</html>
