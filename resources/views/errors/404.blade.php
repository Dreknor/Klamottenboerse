@extends('errors.layout')
@section('code', 'Nicht gefunden')
@section('titel', 'Diese Seite gibt es nicht (mehr)')
@section('inhalt')
    <p>{{ \App\Support\Fehlermeldung::eigene($exception) ?? 'Vielleicht ist der Link veraltet oder der Eintrag wurde inzwischen gelöscht.' }}</p>
    <p class="leise">Wenn du über einen Link in einer E-Mail hierher gekommen bist, ist er eventuell abgelaufen – dann lass dir einfach einen neuen schicken.</p>
@endsection
