@extends('layouts.app')

@section('content')
 <div class="container">
     <div class="row">
         <div class="col-md-6">
             <div class="panel panel-default">
                 <div class="panel-heading">
                     Verkäufernummern

                 </div>

                 <div class="panel-body">
                     @if(isset($Nummern) and count($Nummern) > 0)


                             @foreach($Nummern AS $Nummer)

                                 @if($Nummer->vergeben_an != "")
                                    <a href="#" class="list-group-item list-group-item-info clearfix">
                                        {{ $Nummer->vknummer }}
                                        <span class="pull-right">
                                            <button class="btn btn-xs btn-info">Vergabe aufheben</button>
                                            <button class="btn btn-xs btn-warning">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                          </span>

                                        @if($Nummer->reserviert_fuer != "")
                                            <span class="badge"> {{$Nummer->vorname}} {{$Nummer->nachname}}</span>
                                        @endif
                                 @else
                                    <a href="#" class="list-group-item clearfix">
                                        {{ $Nummer->vknummer }}
                                        <span class="pull-right">

                                            <button class="btn btn-xs btn-warning">
                                                <span class="glyphicon glyphicon-trash"></span>
                                            </button>
                                          </span>

                                        @if($Nummer->reserviert_fuer != "")
                                            <span class="badge"> {{$Nummer->vorname}} {{$Nummer->nachname}}</span>
                                        @endif
                                 @endif




                                 </a>
                             @endforeach
                         </ul>
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