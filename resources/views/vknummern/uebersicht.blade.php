@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-11">
                                Verkäufernummern
                            </div>
                            <div class="col-lg-1">
                                <a href="{{ url('Nummern/new') }}" class="glyphicon glyphicon-plus" title="neue Nummer anlegen"></a>
                            </div>
                        </div>

                    </div>

                    <div class="panel-body">
                        @if(isset($Nummern) and count($Nummern) > 0)
                            <div class="row">

                                @foreach($Nummern AS $Nummer)
                                    <span>
                                 @if($Nummer->vergeben_an == "")

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default">{{ $Nummer->vknummer }}</button>
                                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="#">vergeben</a></li>
                                                    <li role="separator" class="divider"></li>
                                                    @if($Nummer->reserviert_fuer != "")
                                                        <li class="dropdown-header">reserviert für:</li>
                                                        <li class="dropdown-header">{{$Nummer->reserviert->vorname}} {{$Nummer->reserviert->nachname}}</li>
                                                        <li><a href="{{ url("Nummern/$Nummer->reserviert_fuer/aufheben") }}">aufheben</a></li>
                                                        <li role="separator" class="divider"></li>
                                                    @endif
                                                    <li><a href="#">Nummer löschen</a></li>
                                                </ul>
                                            </div>


                                        @else

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success">{{ $Nummer->vknummer }}</button>
                                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li class="dropdown-header">vergeben an:</li>
                                                    <li class="dropdown-header">{{$Nummer->vergeben->vorname}} {{$Nummer->vergeben->nachname}}</li>
                                                    <li><a href="#">Vergabe löschen</a></li>
                                                    <li role="separator" class="divider"></li>
                                                    @if($Nummer->reserviert_fuer != "")
                                                        <li class="dropdown-header">reserviert für:</li>
                                                        <li class="dropdown-header">{{$Nummer->reserviert->vorname}} {{$Nummer->reserviert->nachname}}</li>
                                                        <li><a href="#">aufheben</a></li>
                                                        <li role="separator" class="divider"></li>
                                                    @endif
                                                    <li><a href="#">Nummer löschen</a></li>
                                                </ul>
                                            </div>
                                        @endif

                             </span>
                                @endforeach
                            </div>
                        @else
                            Keine Nummern angelegt
                        @endif
                    </div>

                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-lg-6">
                                vergebene Nummern: {{ $Count['vergeben'] }} / {{ $Count['gesamt'] }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection