@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 640px;">
    <div class="app-card p-4 mt-4">
        <h3 class="mb-3">Registrierung löschen</h3>

        <p>Gib deine registrierte E-Mail-Adresse ein. Wir senden dir einen
            Bestätigungslink. Nach der Bestätigung wird deine Registrierung
            zunächst gesperrt und nach 30 Tagen Karenzzeit endgültig gelöscht.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="post" action="{{ url('registrieren/loeschen') }}">
            @csrf
            <div class="form-group">
                <label for="mail">E-Mail-Adresse</label>
                <input type="email" name="mail" id="mail" class="form-control" required value="{{ old('mail') }}">
            </div>

            <button type="submit" class="app-button">Löschung anfragen</button>
        </form>
    </div>
</div>
@endsection
