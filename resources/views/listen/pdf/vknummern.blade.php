<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap.css')}}"/>


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
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th height="40px">Verkäufernummer</th>
                    <th height="40px">Verkäufer</th>
                    <th height="40px">Telefon</th>
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
                                        <h1>Verkäufer - Übersicht</h1>
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
                                <th height="40px">Verkäufernummer</th>
                                <th height="40px">Verkäufer</th>
                                <th height="40px">Telefon</th>
                            </tr>
                    @endif

                  <tr >
                    <th width="25%" height="40px">
                        {{ $Nummer->vknummer }}
                    </th>
                    <td width="40%" height="40px">
                        @if(isset($Nummer->vorname))
                            {{$Nummer->vorname}} {{$Nummer->nachname}}
                        @endif
                    </td>
                    <td width="35%" height="40px">
                        @if(isset($Nummer->telefon))
                            <p>{{$Nummer->telefon}} </p>
                        @endif

                        @if(isset($Nummer->handy))
                            <p>{{$Nummer->handy}}</p>
                        @endif
                    </td>
                </tr>






            @endforeach
            </table>


        </div>
    </div>

</body>

</html>