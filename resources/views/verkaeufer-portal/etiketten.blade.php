<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Etiketten VK {{ $vknummer->vknummer }}</title>
    <style>
        @page { margin: 8mm; }
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; }
        .labels { display: flex; flex-wrap: wrap; gap: 4mm; }
        .label {
            width: 55mm;
            height: 30mm;
            border: 1px dashed #94a3b8;
            border-radius: 4px;
            padding: 3mm;
            box-sizing: border-box;
            page-break-inside: avoid;
        }
        .label .vknummer { font-size: 18pt; font-weight: bold; }
        .label .artikelnummer { font-size: 10pt; color: #475569; }
        .label .preis { font-size: 16pt; font-weight: bold; text-align: right; margin-top: 2mm; }
        .label .beschreibung { font-size: 9pt; color: #334155; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .no-print { margin-bottom: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Drucken</button>
    </div>

    <div class="labels">
        @foreach ($artikel as $a)
            <div class="label">
                <div class="vknummer">{{ $vknummer->vknummer }}</div>
                <div class="artikelnummer">Artikel-Nr. {{ $a->artikelnummer }}</div>
                <div class="beschreibung">{{ $a->beschreibung }}</div>
                @if ($a->groesse)
                    <div class="beschreibung">Größe: {{ $a->groesse }}</div>
                @endif
                <div class="preis">{{ number_format($a->preis, 2) }} €</div>
            </div>
        @endforeach
    </div>
</body>
</html>
