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
        <div class="container">
            <div class="row">
                <div class="col-md-5">
                    <img src="{{ asset('img/header.png') }}" style="width: 100%"  >
                </div>
                <div class="col-md-6">
                    <h1 align="center">Abrechnung Klamottenbörse</h1>
                </div>
            </div>


        </div>


        <p align="right">
            <br>
            Datum: {{ $Settings->datum->format('d.m.Y') }}
            <br><br>
        </p>
        <br>
        <p>
            <table width="80%" style="margin-left: 50px;">
                <tr>
                    <th align="left">Gesamtumsatz:</th>
                    <td align="right">{{ sprintf('%s', number_format($Umsatz, 2).' €')  }}</td>
                </tr>
                <tr>
                    <th align="left">Erlös Kinderhaus:</th>
                    <td align="right">{{ sprintf('%s', number_format($Erloes, 2).' €')  }}</td>
                </tr>
                <tr>
                    <th align="left">verkaufte Teile:</th>
                    <td align="right">{{ $Teile  }}</td>
                </tr>
                <tr>
                    <th align="left">Kunden:</th>
                    <td align="right">{{ $Kunden  }}</td>
                </tr>
            <tr>
                <th align="left">erfolgreichster Verkäufer:</th>
                @if($erfolgreichsteVKnummer)
                    <td align="right">{{ $erfolgreichsteVKnummer->vergeben_an_Interessent->vorname }} {{ $erfolgreichsteVKnummer->vergeben_an_Interessent->nachname }}
                        {{ $verkaufteArtikel->where('vknummer', $erfolgreichsteVKnummer->vknummer)->count() }} Teile für {{ sprintf('%s', number_format($verkaufteArtikel->where('vknummer', $erfolgreichsteVKnummer->vknummer)->sum('betrag'), 2).' €')  }}
                    </td>
                @else
                    <td align="right">Keine Verkäufer</td>
                @endif


            </tr>
        </table>
        </p>
        <div class="page-break"></div>
        <table width="80%" style="margin-left: 50px;">
            <tr>
              <th>Umsatz</th>
              <th>VK-Nummer</th>
                <th>Name</th>
            </tr>
        @foreach($Vknummern as $Datensatz)

            <tr style="border-bottom: 1px solid black; text-align: center;">
                <td>
                    {{$Datensatz->vknummer}}
                </td>
                <td>{{ sprintf('%s', number_format($Datensatz->umsatz, 2).' €')  }}</td>
                <td>
                    {{ $Datensatz->vergeben_an_Interessent->vorname }} {{ $Datensatz->vergeben_an_Interessent->nachname }}
                </td>
            </tr>


        @endforeach

        </table>
        <div class="page-break"></div>

        @foreach($Vknummern as $Verkaeufer)
            @php($SummeVK =0)
            @php(set_time_limit ( 60 ))
            <div class="page-break"></div>

                        <h1>
                            <img src="{{ asset('img/headerAbrechnung2.png') }}" height="160px">
                            <span align="center">Verkäuferabrechnung</span>
                        </h1>

            <p align="right">
                Datum: {{ $Settings->datum->format('d.m.Y') }}
                <br>
            </p>

            <p style="margin-left: 50px; font-size: larger;">
                <b>Verkäufer:</b> {{ $Verkaeufer->vorname }} {{ $Verkaeufer->nachname }} <br>
                <b>Verkäufernummer:</b> <span style="font-size: 25px;">{{ $Verkaeufer->vknummer }}</span>
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
