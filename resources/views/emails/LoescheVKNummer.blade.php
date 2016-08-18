@if($Interessent->anrede == 'Frau')
    Sehr geehrte Frau {!! $Interessent->nachname !!},
@else
    Sehr geehrter Herr {!! $Interessent->nachname !!},
@endif

Die Vergabe der an Sie vergebenen Verkäufernummer wurde aufgehoben, sodass Sie nicht weiter als Verkäufer für die anstehende Klamottenbörse geführt werden.
Sollten Sie dazu Fragen haben, können Sie uns gern anschreiben.

Liebe Grüße
Das Team der Klamottenbörse

----------------
Sollten Sie kein Interesse mehr daran haben an der Klamottenbörse teilzunehmen, so rufen Sie bitte folgenden Link in Ihrem Browser auf.
Sie werden dann vollständig aus unserer Datenbank gelöscht.

{{ url("/$Interessent->id/abmelden/".$Interessent->mail) }}