@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-6 col-lg-offset-2">
            <form action="{{url('Nummern/new')}}" method="post">
                {{csrf_field()}}
                <p>Welche Verkäufernummer soll neu angelegt werden?</p>
                <p><input class="input-group" name="vknummer" type="number" min="100" max="999" >
                    <input type="hidden" value="{{ $Klamottenboerse }}" name="klamottenboersen_id"></p>
                <p><button class="btn btn-success" type="submit" name="anlegen" >anlegen</button></p>
            </form>
        </div>
    </div>
@endsection