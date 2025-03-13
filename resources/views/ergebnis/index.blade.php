@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3>Ergebnisse</h3>
            </div>
            <div class="card-body">
                <p>
                    Damit die Ergebnisse der Klamottenbörse sicher abgerufen werden können, müssen Sie hier Ihre E-Mail-Adresse eingeben.
                    Sie erhalten dann eine E-Mail mit einem Link, über den Sie die Ergebnisse abrufen können.
                </p>
            </div>
            @if(session()->has('message'))
                <div class="alert alert-info">
                    {{ session()->get('message') }}
                </div>
            @endif
            <div class="card-footer border-top">
                <form action="{{ route('ergebnis.mail') }}" method="post">
                    @csrf
                    <div class="form-group row">
                        <label for="email" class="col-md-2 col-form-label">E-Mail-Adresse</label>
                        <div class="col-md-10">
                            <input type="email" name="email" class="form-control" value="{{old('email')}}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6 offset-md-4">
                            <button type="submit" class="btn btn-primary">Ergebnisse abrufen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
