<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 14mm 14mm 16mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #000; }
    h1 { font-size: 15pt; margin: 0 0 1mm; }
    .unter { color: #444; margin-bottom: 4mm; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #000; padding: 1.5mm 2mm; text-align: left; vertical-align: top; }
    th { background: #eee; font-size: 9pt; }
    td.nr { font-weight: bold; font-size: 12pt; text-align: center; width: 14mm; }
    td.bem { width: 62mm; }
    tr { page-break-inside: avoid; }
    .block { page-break-after: always; }
    .block:last-child { page-break-after: auto; }
</style>
</head>
<body>
@forelse ($bloecke as $start => $teilnahmen)
    <div class="block">
        <h1>Verkäufer {{ $start }}–{{ $start + 99 }}</h1>
        <div class="unter">{{ $boerse->titel }} · {{ $boerse->verkaufstag->isoFormat('dddd, D. MMMM YYYY') }} · {{ $teilnahmen->count() }} Verkäufer</div>
        <table>
            <thead><tr><th>Nr.</th><th>Verkäufer</th><th>Telefon</th><th>Bemerkung</th></tr></thead>
            <tbody>
            @foreach ($teilnahmen as $t)
                <tr>
                    <td class="nr">{{ $t->nummer }}</td>
                    <td>{{ $t->anzeigeName() }}</td>
                    <td>{{ $t->person?->telefon }}</td>
                    <td class="bem">&nbsp;</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@empty
    <p>Noch keine Verkäufer mit Nummer.</p>
@endforelse
</body>
</html>
