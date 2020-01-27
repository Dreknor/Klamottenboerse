@if($Interessent->anrede == 'Frau')
    Liebe {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@elseif ($Interessent->anrede == 'Familie')
    Liebe Familie {!! $Interessent->nachname !!},
@else
    Lieber {!! $Interessent->vorname !!} {!! $Interessent->nachname !!},
@endif

Wir haben Sie aus unserem Verteiler gelöscht.

Liebe Grüße
{{auth()->user()->name}}
