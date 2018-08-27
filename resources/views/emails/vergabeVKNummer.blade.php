@if($Interessent->anrede == 'Frau')
   Liebe {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@elseif($Interessent->anrede == 'Herr')
    Lieber {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@else
    Liebe Familie {!! $Interessent->nachname !!},
@endif

vielen Dank für Ihre Anmeldung zur Klamottenbörse.
Ihre Verkäufernummer für die Klamottenbörse am {{$Klamottenboerse->datum->format('d.m.Y')}} finden Sie zusammen mit allen wichtigen Informationen im Anhang.

Falls Sie weit unter der maximalen Stückanzahl ({{$Klamottenboerse->maxTeile}} Teile) liegen sollten, dann teilen Sie uns das bitte rechtzeitig mit, damit noch ein weiterer Verkäufer zusätzlich eine Chance bekommen kann.

@if($Interessent->telefon == "" and $Interessent->handy =="")
Wir benötigen von Ihnen noch eine Telefonnummer über die wir Sie bei Rückfragen erreichen können.
@endif

Und nun noch ein wichtiger Hinweis an alle, da es in der Vergangenheit öfter zu Problemen kam:
Bitte beachten Sie die genaue Etikettenbeschriftung und achten Sie darauf das die Kleber auch wirklich halten.
Besonders bei Jacken, Kleidern und anderer Hängeware ist es wichtig zwei Kleber zu benutzen (einen innen und einen außen),
da beim Durchschauen der Ware an den Ständern leider viel zu schnell die Kleber abgehen und wir dann diese nicht mehr dem jeweiligen Teil zuordnen können.

Liebe Grüße
das Klamottenbörsen-Team
{!!  $Absender !!}