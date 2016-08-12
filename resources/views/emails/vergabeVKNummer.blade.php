@if($Interessent->anrede == 'Frau')
    Sehr geehrte Frau {!! $Interessent->nachname !!},
@else
    Sehr geehrter Herr {!! $Interessent->nachname !!},
@endif

Für die anstehende Klamottenbörse haben wir Ihnen folgende Verkäufernummer zugewiesen:

{{$VKNummer->vknummer}}

Bitte beachten Sie die angehangenen Informationen für Verkäufer.

@if($Interessent->telefon == "")
    Wir benötigen von Ihnen noch eine Telefonnummer über die wir Sie bei Rückfragen erreichen können.
@endif

Liebe Grüße
Das Team der Klamottenbörse

----------------
Sollten Sie kein Interesse mehr daran haben an der Klamottenbörse teilzunehmen, so rufen Sie bitte folgenden Link in Ihrem Browser auf.
Sie werden dann vollständig aus unserer Datenbank gelöscht.

{{ url("/$Interessent->id/abmelden/$Interessent->mail") }}