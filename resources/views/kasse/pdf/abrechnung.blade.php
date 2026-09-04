<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Klamottenbörsenabrechnung</title>

    <link rel="stylesheet" href="{{ asset('css/bootstrap.css') }}">
    <style>
        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>

    @php($Datum = $Settings->datum->format('d.m.Y'))

    <div class="container">
        <div class="col-lg-12">
            <p class= "text-center" style="font-size: 2em;">
               Abrechnung der Klamottenbörse
            </p>
        </div>
    </div>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <p align="right">
                <br>
                Datum: {{ $Datum }}
                <br><br>
            </p>
        </div>
    </div>

    <div class="col-md-2 col-md-offset-2">
        <br><br>
        <div class="panel panel-default">
            <div class="panel-heading">
                <p>
                    Übersicht
                </p>
            </div>
            <div class="panel-body">
                <ul class="list-group">
                    <li class="list-group-item">
                        Gesamtumsatz: <span class="badge">  {{ sprintf('%s', number_format($Umsatz, 2).' €')  }}</span><br><br>
                    </li>
                    <li class="list-group-item">
                        Kinderhaus: <span class="badge">  {{ sprintf('%s', number_format($Erloes, 2).' €')  }}</span><br><br>
                    </li>
                    <li class="list-group-item">
                        Kunden: <span class="badge"> {{ $Kunden }}</span><br><br>
                    </li>
                    <li class="list-group-item">
                        verkaufte Artikel: <span class="badge"> {{ $Teile  }}</span><br><br>
                    </li>

                </ul>
            </div>
        </div>
    </div>
</div>

<div class="page-break"></div>

    @foreach($Vknummern as $Verkaeufer)
        @php($SummeVK =0)
        <div class="container">

                <p class= "text-center" style="font-size: 2em;">
                    Abrechnung der Klamottenbörse
                </p>
            </div>
        </div>

                <p align="right">
                    <br>
                    Datum: {{ $Datum }}
                    <br><br>
                </p>

        <p>
            <b>Verkäufer:</b> {{ $Verkaeufer->vorname }} {{ $Verkaeufer->nachname }}<br>
            <b>Verkäufernummer:</b> {{ $Verkaeufer->vknummer }}
        </p>

        <p>
            @if(count($Verkaeufer->verkaufteArtikel) >0)
                @foreach($Verkaeufer->verkaufteArtikel as $Artikel)
                    <br>
                       {{ $Artikel->vknummer }}
                       {{ $Artikel->artikelnummer }}
                       {{ sprintf('%s', number_format($Artikel->betrag, 2).' €')  }}

                    @php($SummeVK+=$Artikel->betrag)
                @endforeach
            @else
                    <br>Es wurden keine Artikel verkauft<br>
            @endif
        </p>


        <div class="page-break"></div>

    @endforeach
</body>

</html>

