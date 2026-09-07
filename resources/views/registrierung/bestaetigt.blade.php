@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 640px;">
    <div class="app-card p-4 mt-4 text-center">
        <h3 class="mb-3">Vielen Dank, {{ $interessent->vorname }}!</h3>
        <p>Deine E-Mail-Adresse wurde erfolgreich bestätigt. Wir melden uns, sobald deine Registrierung bearbeitet wurde.</p>
    </div>
</div>
@endsection
