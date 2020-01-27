<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>

    <style>
        body{
            margin: 8px;
        }

        .spalte {
            width: 13%;
            float: left;
            align-items: center;
            margin: 3px;

        }



    </style>
</head>

<body>
    <h3>Vergebene Verkäufernummern</h3>

    @for ($i = 200; $i < 700; $i+=100)
                <div class="spalte">
                    @foreach($Nummern->where('vknummer', ">=", $i)->where('vknummer', "<", ($i+100))->all()  AS $Nummer)
                        {{$Nummer->vknummer}}<br>
                        
                    @endforeach
                </div>

    @endfor
</body>

</html>
