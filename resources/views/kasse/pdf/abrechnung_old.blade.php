<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Abrechnung</title>

    <style>
        .page-break {
            page-break-before: always;
        }

        body {
            font-size: larger;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }
        td,
        th {
            padding: 0;
        }

        .table {
            border-collapse: collapse !important;
           cellpadding: 5px;
        }
        .table td,
        .table th {
            background-color: #fff !important;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid lightslategrey !important;
        }
    </style>

</head>
    <body>
        <h1 align="center">Abrechnung Klamottenbörse</h1>

        <p align="right">
            <br>
            Datum: {{ $Settings->datum->format('d.m.Y') }}
            <br><br>
        </p>
        <br>
        <p>
            <table width="80%" style="margin-left: 50px;">
                <tr>
                    <th>Gesamtumsatz</th>
                    <td align="right">{{ sprintf('%s', number_format($Umsatz, 2).' €')  }}</td>
                </tr>
                <tr>
                    <th>Erlös Kinderhaus</th>
                    <td align="right">{{ sprintf('%s', number_format($Erloes, 2).' €')  }}</td>
                </tr>
        </table>
        </p>

        @foreach($Vknummern as $Verkaeufer)
            @php($SummeVK =0)
            @php(set_time_limit ( 60 ))
            <div class="page-break"></div>

            <h1 align="center">Abrechnung Klamottenbörse</h1>
            <p align="right">
                Datum: {{ $Settings->datum->format('d.m.Y') }}
                <br>
            </p>

            <p style="margin-left: 50px;">
                <b>Verkäufer:</b> {{ $Verkaeufer->vorname }} {{ $Verkaeufer->nachname }} <br>
                <b>Verkäufernummer:</b> {{ $Verkaeufer->vknummer }}
            </p>
            <hr>
            <p>


            @if(count($Verkaeufer->verkaufteArtikel) >0 )
                <table width="100%" class="table table-striped">
                    <tr >
                        <th>Verkäufernummer</th>
                        <th align="left">Artikel</th>
                        <th align="right" style="padding-right: 50px;">Betrag</th>
                    </tr>

                            @foreach($Verkaeufer->verkaufteArtikel as $Artikel)
                                <tr >
                                    <td align="center" >{{ $Artikel->vknummer }}</td>
                                    <td >{{ $Artikel->artikelnummer }}</td>
                                    <td  align="right" style="padding-right: 50px;">{{ sprintf('%s', number_format($Artikel->betrag, 2).' €')  }}</td>
                                </tr>
                                @php($SummeVK+=$Artikel->betrag)
                            @endforeach

                    <tr align="right">
                        <th colspan="2" >Summe</th>
                        <td style="padding-right: 50px;border-top: 1px dashed black;">{{ sprintf('%s', number_format($SummeVK, 2).' €')  }}</td>
                    </tr>
                    <tr align="right">
                        <th colspan="2" >Gebühr: ({{ $Settings->provision }}%)</th>
                        <td style="padding-right: 50px;border-top: 1px dashed black;">{{ sprintf('%s', number_format(round($SummeVK/100*$Settings->provision,2), 2).' €')  }}</td>
                    </tr>
                    <tr align="right">
                        <th colspan="2" >Auszahlung: </th>
                        <td style="padding-right: 50px; border-top: 1px dashed black;">{{ sprintf('%s', number_format($SummeVK - round($SummeVK/100*$Settings->provision,2), 2).' €')  }}</td>
                    </tr>

                </table>

            @else
               <br>Es wurden keine Artikel verkauft<br></td>

            @endif
            </p>
        @endforeach
    </body>
</html>