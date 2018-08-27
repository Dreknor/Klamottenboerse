<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Belehrungen</title>

    <!-- Styles -->



</head>

<body>


@foreach($Nummern AS $Nummer)
    @php(set_time_limit ( 60))
            <table style="width: 100%;">
                <tr>
                    <td style="width: 70%; align: left; vertical-align: top">
                        <p style="font-size: large;"><b>Name:</b> {{ $Nummer->vorname }} {{ $Nummer->nachname }} </p>
                        <p style="font-size: large;"><b>Telefon:</b> {{ $Nummer->telefon }} {{ $Nummer->handy }}</p>
                    </td>
                    <td style="width: 30%;  height: 80px; border: 1px solid black; vertical-align: top;">
                        <p>Verkäufernummer:</p>
                        <p style="width: 100%; text-align: center; font-size: 2em;"><b>{{ $Nummer->vknummer }}</b></p>
                    </td>
                </tr>
            </table>

            <p><br>
                Ich bin darüber informiert, dass die Elternvertretung des Evangelischen Kinderhauses der Friedenskirchgemeinde als Veranstalter der Klamottenbörse keine Haftung für abhanden gekommende Waren übernimmt, wenn gleich sorgfältig darauf geachtet wird, dass dies nicht passiert.
            </p>
            <p>Die nicht verkaufte Ware muss am Tag der Klamottenbörse zwischen {{$Klamottenboerse->datum->format('d.m.Y')}} von  @php
                    echo date('G.i', strtotime($Klamottenboerse->abholung_von));
                @endphp
                und
                @php
                    echo date('G.i', strtotime($Klamottenboerse->abholung_bis));
                @endphp Uhr  im Lutherhaus abgeholt werden.</p>
            <p> 25% des Verkaufserlöses ist für das Kinderhaus bestimmt.</p>



            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; align: left; vertical-align: top">
                        <p><br>Radebeul, den
                            @php
                                echo date('d.m.Y', strtotime($Klamottenboerse->datum."-1 day"));
                            @endphp  </p>
                    </td>
                    <td style="width: 50%; align: left; vertical-align: top">
                        <p><br>Unterschrift: _______________________________</p>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p><br>
                            <b>Verkaufserlös erhalten:</b> _________________________________
                        </p>
                    </td>
                </tr>
            </table>

            @if(isset($Trenner))
                <div  style="page-break-after: always;"></div>
                @php
                    unset($Trenner);
                @endphp

            @else
<hr><br>
                @php
                    $Trenner=TRUE;
                @endphp

            @endif

@endforeach
</body>

</html>