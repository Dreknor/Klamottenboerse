@extends('layouts.app')

@section('content')
<div class="container-fluid" style="max-width: 640px;">
    <div class="app-card p-4 mt-4">
        <h3 class="mb-3">Registrierung als Interessent</h3>

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

        <form method="post" action="{{ url('registrieren') }}">
            @csrf
            <input type="hidden" name="form_rendered_at" value="{{ $formRenderedAt }}">

            {{-- Honeypot field: hidden from real users via CSS, bots tend to fill every field. --}}
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="anrede">Anrede</label>
                <select name="anrede" id="anrede" class="form-control" required>
                    <option value="">Bitte wählen</option>
                    <option value="Frau">Frau</option>
                    <option value="Herr">Herr</option>
                    <option value="Familie">Familie</option>
                </select>
            </div>

            <div class="form-group">
                <label for="vorname">Vorname</label>
                <input type="text" name="vorname" id="vorname" class="form-control" required value="{{ old('vorname') }}">
            </div>

            <div class="form-group">
                <label for="nachname">Nachname</label>
                <input type="text" name="nachname" id="nachname" class="form-control" required value="{{ old('nachname') }}">
            </div>

            <div class="form-group">
                <label for="mail">E-Mail-Adresse</label>
                <input type="email" name="mail" id="mail" class="form-control" required value="{{ old('mail') }}">
            </div>

            <div class="form-group">
                <label for="telefon">Telefon (optional)</label>
                <input type="text" name="telefon" id="telefon" class="form-control" value="{{ old('telefon') }}">
            </div>

            <div class="form-group">
                <label for="handy">Handy (optional)</label>
                <input type="text" name="handy" id="handy" class="form-control" value="{{ old('handy') }}">
            </div>

            <button type="submit" class="app-button">Registrieren</button>
        </form>
    </div>
</div>
@endsection
