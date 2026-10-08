<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<style>
    @page { margin: 14mm 16mm 16mm; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.45; color: #1c1917; }
    .kopf { border-bottom: 3px solid #FF6900; padding-bottom: 3mm; margin-bottom: 5mm; }
    .kopf table { width: 100%; }
    .kopf img { height: 16mm; }
    .kopf td.titel { width: 68%; }
    .kopf h1 { font-size: 17pt; margin: 0; color: #c2410c; }
    .kopf .unter { color: #57534e; }
    h2 { font-size: 12pt; margin: 0 0 1.5mm; color: #c2410c; }
    .block { margin-bottom: 4.5mm; }
    .block h2 { page-break-after: avoid; }
    .hinweis, table.termin { page-break-inside: avoid; }
    .block p { margin: 0 0 1.8mm; }
    .block ul { margin: 0; padding-left: 5mm; }
    .hinweis { border-left: 3px solid #FF6900; background: #fff7ed; padding: 2.5mm 3.5mm; }
    .hinweis.grau { border-left-color: #a8a29e; background: #f5f5f4; }
    table.termin { width: 100%; border-collapse: collapse; }
    table.termin td { padding: 1.5mm 2mm; border: 1px solid #e7e5e4; vertical-align: top; }
    table.termin td.was { width: 32mm; font-weight: bold; color: #9a3412; }
    table.spalten { width: 100%; }
    table.spalten td { width: 50%; vertical-align: top; padding-right: 4mm; }
    .frage { font-weight: bold; margin-top: 1.5mm; }
    .fuss { margin-top: 6mm; padding-top: 2mm; border-top: 1px solid #d6d3d1; font-size: 9pt; color: #57534e; }
</style>
</head>
<body>
<div class="kopf">
    <table>
        <tr>
            <td class="titel">
                <h1>Wichtige Infos für Verkäufer</h1>
                <div class="unter">{{ $k->boerse?->titel }}@if ($k->boerse) · {{ $k->infos['datum'] }}@endif</div>
            </td>
            @if ($logo = \App\Support\PdfBild::datenUri(public_path('images/logo-schriftzug.png')))
                <td style="text-align: right;"><img src="{{ $logo }}" alt=""></td>
            @endif
        </tr>
    </table>
</div>

@foreach ($bloecke as $b)
    @switch($b['typ'])
        @case('termin')
            @if ($k->boerse)
                <div class="block">
                    @if ($b['titel'])<h2>{{ $b['titel'] }}</h2>@endif
                    <table class="termin">
                        <tr><td class="was">Verkauf</td><td>{{ $k->infos['datum'] }}{{ $k->infos['verkauf'] ? ', '.$k->infos['verkauf'] : '' }}</td></tr>
                        <tr><td class="was">Kisten abgeben</td><td>{{ $k->infos['anlieferung'] ?: 'wird noch bekannt gegeben' }}</td></tr>
                        <tr><td class="was">Abholung</td><td>{{ $k->infos['abholung'] ?: 'wird noch bekannt gegeben' }}</td></tr>
                        <tr><td class="was">Ort</td><td>{{ $k->infos['ort'] }}</td></tr>
                    </table>
                </div>
            @endif
            @break
        @case('kopf')
        @case('text')
            @if ($b['titel'] || $b['text'])
                <div class="block">
                    @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
                    {!! $k->text($b['text']) !!}
                </div>
            @endif
            @break
        @case('hinweis')
            <div class="block hinweis {{ ($b['farbe'] ?? '') === 'grau' ? 'grau' : '' }}">
                @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
                {!! $k->text($b['text']) !!}
            </div>
            @break
        @case('liste')
            <div class="block">
                @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
                <ul>@foreach ($b['eintraege'] as $eintrag)<li>{{ $k->zeile($eintrag) }}</li>@endforeach</ul>
            </div>
            @break
        @case('zwei_spalten')
            <div class="block">
                <table class="spalten"><tr>
                    <td>@if ($b['links_titel'])<h2>{{ $k->zeile($b['links_titel']) }}</h2>@endif{!! $k->text($b['links']) !!}</td>
                    <td>@if ($b['rechts_titel'])<h2>{{ $k->zeile($b['rechts_titel']) }}</h2>@endif{!! $k->text($b['rechts']) !!}</td>
                </tr></table>
            </div>
            @break
        @case('faq')
            <div class="block">
                @if ($b['titel'])<h2>{{ $k->zeile($b['titel']) }}</h2>@endif
                @foreach ($b['eintraege'] as $e)
                    <div class="frage">{{ $k->zeile($e['frage']) }}</div>
                    {!! $k->text($e['antwort']) !!}
                @endforeach
            </div>
            @break
    @endswitch
@endforeach

<div class="fuss">
    Rückfragen gern per Mail{{ $kontakt['email'] ? ' an '.$kontakt['email'] : '' }}{{ $kontakt['telefon'] ? ', in dringenden Fällen unter '.$kontakt['telefon'] : '' }}.
    Dein persönliches Portal (Nummer, Artikel, Etiketten, Abrechnung): {{ route('portal.link') }}
</div>
</body>
</html>
