<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>

    <!-- Styles
    <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap.css')}}"/>
    -->
    <style rel="stylesheet" type="text/css">
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
        }
        .table td,
        .table th {
            background-color: #fff !important;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid black !important;
            max-height: 30px;
            font-size: smaller;
        }


    </style>

</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h1>Verkäufer - Übersicht</h1>
                        Klamottenbörse am {{ $Klamottenboerse->datum->format('d.m.Y') }}
                    </div>
                </div>
            </div>
        </div>

        <div class="panel-body">
            <table class="table-bordered" width="100%">
                <tr>
                    <th style="width: 10%;"></th>
                    <th style="width: 20%;"></th>
                    <th style="width: 70%;"></th>
                </tr>
                <tr>
                    <th height="30px" style="width: 10%;">Verkäufernummer</th>
                    <th height="30px" style="width: 20%;">Verkäufer</th>
                    <th height="30px" style="width: 70%;">Telefon</th>
                </tr>

            @foreach($Nummern AS $Nummer)
                    @if(number_format(floor($Nummer->vknummer/100)*100,0) > $alteNummer)
                        </table>

                        @php
                            $alteNummer = number_format(floor($Nummer->vknummer/100)*100,0);
                        @endphp

                        <div  style="page-break-after: always;"></div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h2>Verkäufer - Übersicht</h2>
                                        Klamottenbörse am {{ $Klamottenboerse->datum->format('d.m.Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table-bordered" width="100%">
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr>
                                <th height="30px" >Verkäufernummer</th>
                                <th height="30px">Verkäufer</th>
                                <th height="30px" >Telefon</th>
                            </tr>
                    @endif

                  <tr >
                    <th width="25%" height="30px">
                        {{ $Nummer->vknummer }}
                    </th>
                    <td width="40%" height="30px" style="text-align: center;">
                        @if(isset($Nummer->vorname))
                            {{$Nummer->vorname}} {{$Nummer->nachname}}
                        @endif
                    </td>
                    <td width="35%" height="30px" style="text-align: center;">
                        @if(isset($Nummer->telefon))
                             {{$Nummer->telefon}}
                        @endif
<br>
                        @if(isset($Nummer->handy))
                            {{$Nummer->handy}}
                        @endif
                    </td>
                </tr>






            @endforeach
            </table>


        </div>
    </div>

</body>

</html>