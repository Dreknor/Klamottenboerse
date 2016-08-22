<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Helferliste</title>

    <!-- Styles -->



</head>

<body>

<h2>Helferliste - Klamottenbörse</h2>
<table style="width: 100%; ">
    <tr>
        <td>Name</td>
        <td>Telefon</td>
        <td>E-Mail</td>
        <td>Bereich</td>
    </tr>

    @foreach($Klamottenboerse->helfer AS $Helfer)
        <tr >
            <td style="border-bottom: 1px solid black; padding: 15px;">{{ $Helfer->name }}</td>
            <td style="border-bottom: 1px solid black;">{{ $Helfer->telefon }}</td>
            <td style="border-bottom: 1px solid black;">{{ $Helfer->mail }}</td>
            <td style="border-bottom: 1px solid black;">{{ $Helfer->bereich }}</td>
        </tr>
    @endforeach
</table>


</body>

</html>