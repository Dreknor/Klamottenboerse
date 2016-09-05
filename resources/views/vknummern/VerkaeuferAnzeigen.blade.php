@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-md-3 col-md-offset-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                            <p>Alle Verkäufer mit der Nummer <b>{{ $Nummer }}</b> der letzten Klamottenbörsen (absteigend)</p>
                </div>
                <div class="panel-body">
                    <ul class="list-group">
                        @if(count($Nummer) >0)
                            @foreach($VerkaeuferArray AS $Verkaeufer)
                                @if($Verkaeufer->nachname != "")
                                    <li class="list-group-item"><a href="{{url('/Interessent/'. $Verkaeufer->id )}}">{{ $Verkaeufer->vorname }} {{ $Verkaeufer->nachname }}</a> </li>
                                @else
                                    <li class="list-group-item list-group-item-info"> Nicht vergeben</li>
                                @endif
                            @endforeach
                        @else
                            Bisher gab es keine Verkäufer mit dieser Nummer
                        @endif
                    </ul>
                </div>
                <div class="panel-footer">
                    <p>
                        <a class="glyphicon glyphicon-menu-left pull-right" href="{{ url()->previous() }}">zurück</a>
                    </p>
                </div>
            </div>

        </div>
    </div>
@endsection