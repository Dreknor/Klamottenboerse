@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-6 col-lg-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <p>
                        An wen soll die Nummer {{ $Nummer->vknummer }} vergeben werden?
                        <a class="glyphicon glyphicon-menu-left pull-right" href="{{ url("/Nummern")  }}"> zurück</a>
                    </p>
                </div>
                <div class="panel-body">
                    <form action="{{url('Nummern/vergeben')}}" method="post" class="form-group">
                        {{csrf_field()}}
                        <input type="hidden" value="{{ $Nummer->id }}" name="NummernID">
                        <select class="form-control" name="InteressentenID">
                            @foreach($Interessenten AS $Interessent)
                                <option value="{{ $Interessent->id }}">
                                     {{ $Interessent->nachname }}, {{ $Interessent->vorname }}
                                    @if(isset($Interessent->vknummern_reserviert))
                                         (Reservierte Nummer: {{ $Interessent->vknummern_reserviert->vknummer }} )
                                        @endif
                                </option>
                            @endforeach
                        </select>

                        <p><button class="btn btn-success" type="submit" name="anlegen" >anlegen</button></p>
                    </form>
                </div>
            </div>

        </div>
    </div>
@endsection