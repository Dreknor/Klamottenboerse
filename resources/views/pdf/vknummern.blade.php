<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>


    <!--<link rel="stylesheet" href="{{asset('css/lib/bootstrap/bootstrap.min.css')}}"
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
        .footer {page-break-after: always;}
    </style>

</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card ">
                    <div class="card-header">
                        <h1>Verkäufer - Übersicht</h1>
                        Klamottenbörse am {{ $Klamottenboerse->datum->format('d.m.Y') }}
                    </div>
                </div>
            </div>
        </div>
@for ($i = 200; $i < 700; $i+=100)
        <div class="card">
            <div class="card-body">
            <table class="table table-bordered" width="100%">
                <tr>
                    <th height="30px" style="width: 10%;text-align: center;">Verkäufernummer ({{$i}} - {{$i+100}})</th>
                    <th height="30px" style="width: 20%;text-align: center;">Verkäufer</th>
                    <th height="30px" style="width: 30%;text-align: center;">Telefon</th>
                    <th height="30px" style="width: 40%;text-align: center;">Bemerkung</th>
                </tr>

            @foreach($Nummern->where('vknummer', ">=", $i)->where('vknummer', "<", ($i+100))->all()  AS $Nummer)
                    <tr >
                        <th width="10%" height="30px" style="text-align: center;">
                            {{ $Nummer->vknummer }}
                        </th>
                        <td width="20%" height="30px" style="text-align: center;">
                            @if(isset($Nummer->vergeben_an_Interessent->vorname))
                                {{$Nummer->vergeben_an_Interessent->vorname}} {{$Nummer->vergeben_an_Interessent->nachname}}
                            @endif
                        </td>
                        <td width="30%" height="30px" style="text-align: center;">
                            @if(isset($Nummer->vergeben_an_Interessent->telefon))
                                {{$Nummer->vergeben_an_Interessent->telefon}}
                            @endif
                            <br>
                            @if(isset($Nummer->vergeben_an_Interessent->handy))
                                {{$Nummer->vergeben_an_Interessent->handy}}
                            @endif
                        </td>
                        <td width="40%">
                            <small>
                                {{optional($Nummer->vergeben_an_Interessent->notiz)->notiz}}
                            </small>

                        </td>
                    </tr>
            @endforeach
            </table>
        </div>

        </div>
<div class="footer"></div>
@endfor
    </div>
</body>

</html>