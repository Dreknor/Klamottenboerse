@if($Interessent->anrede == "Herr")
    Lieber {{$Interessent->vorname}} {{$Interessent->nachname}},
@elseif($Interessent->anrede == "Frau")
    Liebe {{$Interessent->vorname}} {{$Interessent->nachname}},
@else
    Liebe Familie {{$Interessent->nachname}},
@endif

vielen Dank für Ihre Anmeldung zur Klamottenbörse.
Ihre Verkäufernummer für die Klamottenbörse am {{$Klamottenboerse->datum->format('d.m.Y')}} finden Sie zusammen mit allen wichtigen Informationen im Anhang.
Falls Sie weit unter der maximalen Stückanzahl ({{$Klamottenboerse->maxTeile}} Teile) liegen sollten, dann teilen Sie uns das bitte rechtzeitig mit, damit noch ein weiterer Verkäufer zusätzlich eine Chance bekommen kann.

Liebe Grüße
{{auth()->user()->name}}