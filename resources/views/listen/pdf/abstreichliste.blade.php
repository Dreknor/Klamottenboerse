<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>

</head>

<body>
    <h2>Vergebene Verkäufernummern</h2>

    <div style="width: 100%">

        @foreach($Spalten AS $key => $Spalte)
            <div style="left:{{50*$key}}pt; width:80px;  position:relative; float: left; ">
                {!! $Spalte !!}
            </div>
        @endforeach
    </div>



</body>

</html>