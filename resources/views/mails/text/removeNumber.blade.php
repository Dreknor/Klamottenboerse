@if($Interessent->anrede == 'Frau')
    Liebe {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@elseif ($Interessent->anrede == 'Familie')
    Liebe Familie {!! $Interessent->nachname !!},
@else
    Lieber {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@endif

Die Vergabe Ihrer Verkäufernummer wurde aufgehoben, sodass Sie nicht weiter als Verkäufer für die anstehende Klamottenbörse geführt werden.
Sollten Sie dazu Fragen haben, können Sie uns gern anschreiben.

Liebe Grüße
{{auth()->user()->name}}
