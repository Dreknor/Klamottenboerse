@extends('errors.layout')
@if (($exception ?? null) instanceof \Illuminate\Routing\Exceptions\InvalidSignatureException)
    @section('code', 'Link abgelaufen')
    @section('titel', 'Dieser Link gilt nicht mehr')
    @section('inhalt')
        <p>Links aus unseren E-Mails sind aus Sicherheitsgründen nur eine begrenzte Zeit gültig – oder der Link wurde beim Kopieren abgeschnitten.</p>
        <p class="leise">Lass dir einfach einen neuen Link schicken, z. B. über „Anmelden“ im Portal.</p>
    @endsection
@else
    @section('code', 'Kein Zugriff')
    @section('titel', 'Dafür fehlt die Berechtigung')
    @section('inhalt')
        <p>{{ \App\Support\Fehlermeldung::eigene($exception) ?? 'Dieser Bereich ist für dein Konto nicht freigeschaltet.' }}</p>
        <p class="leise">Wenn du meinst, dass das ein Versehen ist, bitte das Orga-Team, dir die passende Rolle zu geben.</p>
    @endsection
@endif
