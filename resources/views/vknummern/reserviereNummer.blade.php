@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-4 col-lg-offset-2">
            <form action="{{url('Nummern/'.$Interessent->id.'/reservieren')}}" method="post">
                {{csrf_field()}}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Welche Verkäufernummer soll für <b>{{ $Interessent->vorname}} {{ $Interessent->nachname}}</b> reserviert werden?
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label for="vknummer">Nummer auswählen:</label>
                            <select class="form-control" id="vknummer" name="vknummer">
                                @foreach($Nummern as $Nummer)
                                    <option value="{{$Nummer->id}}">{{$Nummer->vknummer}}</option>
                                @endforeach
                            </select>
                        </div>


                    </div>

                    <div class="panel-footer">
                        <button class="btn btn-success" type="submit" name="anlegen" >anlegen</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
@endsection