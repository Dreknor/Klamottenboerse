@extends('errors.layout')
@section('code', 'Fehler')
@section('titel', 'Das hat leider nicht geklappt')
@section('inhalt')
    <p>{{ $meldung ?? \App\Support\Fehlermeldung::ALLGEMEIN }}</p>
    <p class="leise">Für das Team: Die technischen Details stehen im Backend unter System → Fehlerprotokoll.</p>
@endsection
