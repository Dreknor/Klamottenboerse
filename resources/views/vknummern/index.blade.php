@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <header class="section-header">
            <div class="tbl">
                <div class="tbl-row">
                    <div class="tbl-cell">
                        <h2 id="Ueberschrift">Verkäufernummern</h2>
                        <div class="subtitle" id="subtitle">Nummern der aktuellen Klamottenbörse</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="card">
            <div class="card-header">
                Verkäufernummen
            </div>
            <div class="card-body">
                <div class="row ">
                        @foreach($vknummern->where('vknummer', ">=", 200)->where('vknummer', "<", 300)->all() AS $vknummer)
                            <div class="dropdown m-1 @if ($vknummer->vergeben_an_Interessent != NULL) @elseif ($vknummer->reserviert_fuer_Interessent != NULL) warning @endif"  style="width: 60px;">
                                <button type="button" class="btn btn-sm dropdown-toggle @if ($vknummer->vergeben_an != NULL) btn-success @elseif ($vknummer->reserviert_fuer != NULL) btn-warning @endif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{$vknummer->vknummer}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" >
                                        @if ($vknummer->vergeben_an_Interessent != NULL and is_object($vknummer->vergeben_an_Interessent))
                                            <div class="dropdown-header">vergeben an:</div>
                                                <a class="dropdown-item text-success" href="{{url('interessent/'.$vknummer->vergeben_an_Interessent->id)}}">{{$vknummer->vergeben_an_Interessent->vorname}} {{$vknummer->vergeben_an_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                        @elseif($vknummer->reserviert_fuer_Interessent != NULL and is_object($vknummer->reserviert_fuer_Interessent))
                                            <div class="dropdown-header">reserviert für:</div>
                                                <a class="dropdown-item text-warning" href="{{url('interessent/'.$vknummer->reserviert_fuer_Interessent->id)}}">{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                                <a class="dropdown-item btn btn-success" href="{{url('vknummern/'.$vknummer->id.'/vergeben')}}">Nummer vergeben</a>
                                                <a class="dropdown-item btn btn-danger deleteReservierung" href="#" data-id="{{$vknummer->id}}" data-name="{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}">Reservierung aufheben</a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <div class="dropdown-header">letzte Verkäufer:</div>
                                            @foreach( $vknummer->bisherigeVerkaeufer->take(5) AS $verkaeufer)
                                                <li class="dropdown-item ">
                                                    <a class="small text-black-50" href="{{url('interessent/'.$verkaeufer->vergeben_an_Interessent->id)}}">{{$verkaeufer->vergeben_an_Interessent->vorname}} {{$verkaeufer->vergeben_an_Interessent->nachname}}</a>
                                                </li>
                                            @endforeach
                                        <div class="dropdown-divider"></div>

                                </div>
                            </div>
                        @endforeach
                </div>
                <div class="row ">
                        @foreach($vknummern->where('vknummer', ">=", 300)->where('vknummer', "<", 400)->all() AS $vknummer)
                            <div class="dropdown m-1 @if ($vknummer->vergeben_an_Interessent != NULL) @elseif ($vknummer->reserviert_fuer_Interessent != NULL) warning @endif"  style="width: 60px;">
                                <button type="button" class="btn btn-sm dropdown-toggle @if ($vknummer->vergeben_an != NULL) btn-success @elseif ($vknummer->reserviert_fuer != NULL) btn-warning @endif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{$vknummer->vknummer}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" >
                                        @if ($vknummer->vergeben_an_Interessent != NULL and is_object($vknummer->vergeben_an_Interessent))
                                            <div class="dropdown-header">vergeben an:</div>
                                                <a class="dropdown-item text-success" href="{{url('interessent/'.$vknummer->vergeben_an_Interessent->id)}}">{{$vknummer->vergeben_an_Interessent->vorname}} {{$vknummer->vergeben_an_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                        @elseif($vknummer->reserviert_fuer_Interessent != NULL and is_object($vknummer->reserviert_fuer_Interessent))
                                            <div class="dropdown-header">reserviert für:</div>
                                                <a class="dropdown-item text-warning" href="{{url('interessent/'.$vknummer->reserviert_fuer_Interessent->id)}}">{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                                <a class="dropdown-item btn btn-success" href="{{url('vknummern/'.$vknummer->id.'/vergeben')}}">Nummer vergeben</a>
                                                <a class="dropdown-item btn btn-danger deleteReservierung" href="#" data-id="{{$vknummer->id}}" data-name="{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}">Reservierung aufheben</a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <div class="dropdown-header">letzte Verkäufer:</div>
                                            @foreach( $vknummer->bisherigeVerkaeufer->take(5) AS $verkaeufer)
                                                <li class="dropdown-item ">
                                                    <a class="small text-black-50"  href="{{url('interessent/'.$verkaeufer->vergeben_an_Interessent->id)}}">{{$verkaeufer->vergeben_an_Interessent->vorname}} {{$verkaeufer->vergeben_an_Interessent->nachname}}</a>
                                                </li>
                                            @endforeach
                                        <div class="dropdown-divider"></div>

                                </div>
                            </div>
                        @endforeach
                </div>
                <div class="row ">
                        @foreach($vknummern->where('vknummer', ">=", 400)->where('vknummer', "<", 500)->all() AS $vknummer)
                            <div class="dropdown m-1 @if ($vknummer->vergeben_an_Interessent != NULL) @elseif ($vknummer->reserviert_fuer_Interessent != NULL) warning @endif"  style="width: 60px;">
                                <button type="button" class="btn btn-sm dropdown-toggle @if ($vknummer->vergeben_an != NULL) btn-success @elseif ($vknummer->reserviert_fuer != NULL) btn-warning @endif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{$vknummer->vknummer}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" >
                                        @if ($vknummer->vergeben_an_Interessent != NULL and is_object($vknummer->vergeben_an_Interessent))
                                            <div class="dropdown-header">vergeben an:</div>
                                                <a class="dropdown-item text-success" href="{{url('interessent/'.$vknummer->vergeben_an_Interessent->id)}}">{{$vknummer->vergeben_an_Interessent->vorname}} {{$vknummer->vergeben_an_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                        @elseif($vknummer->reserviert_fuer_Interessent != NULL and is_object($vknummer->reserviert_fuer_Interessent))
                                            <div class="dropdown-header">reserviert für:</div>
                                                <a class="dropdown-item text-warning" href="{{url('interessent/'.$vknummer->reserviert_fuer_Interessent->id)}}">{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                                <a class="dropdown-item btn btn-success" href="{{url('vknummern/'.$vknummer->id.'/vergeben')}}">Nummer vergeben</a>
                                                <a class="dropdown-item btn btn-danger deleteReservierung" href="#" data-id="{{$vknummer->id}}" data-name="{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}">Reservierung aufheben</a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <div class="dropdown-header">letzte Verkäufer:</div>
                                            @foreach( $vknummer->bisherigeVerkaeufer->take(5) AS $verkaeufer)
                                                <li class="dropdown-item ">
                                                    <a class="small text-black-50" href="{{url('interessent/'.$verkaeufer->vergeben_an_Interessent->id)}}">{{$verkaeufer->vergeben_an_Interessent->vorname}} {{$verkaeufer->vergeben_an_Interessent->nachname}}</a>
                                                </li>
                                            @endforeach
                                        <div class="dropdown-divider"></div>

                                </div>
                            </div>
                        @endforeach
                </div>
                <div class="row ">
                        @foreach($vknummern->where('vknummer', ">=", 500)->where('vknummer', "<", 600)->all() AS $vknummer)
                            <div class="dropdown m-1 @if ($vknummer->vergeben_an_Interessent != NULL) @elseif ($vknummer->reserviert_fuer_Interessent != NULL) warning @endif"  style="width: 60px;">
                                <button type="button" class="btn btn-sm dropdown-toggle @if ($vknummer->vergeben_an != NULL) btn-success @elseif ($vknummer->reserviert_fuer != NULL) btn-warning @endif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{$vknummer->vknummer}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" >
                                        @if ($vknummer->vergeben_an_Interessent != NULL and is_object($vknummer->vergeben_an_Interessent))
                                            <div class="dropdown-header">vergeben an:</div>
                                                <a class="dropdown-item text-success" href="{{url('interessent/'.$vknummer->vergeben_an_Interessent->id)}}">{{$vknummer->vergeben_an_Interessent->vorname}} {{$vknummer->vergeben_an_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                        @elseif($vknummer->reserviert_fuer_Interessent != NULL and is_object($vknummer->reserviert_fuer_Interessent))
                                            <div class="dropdown-header">reserviert für:</div>
                                                <a class="dropdown-item text-warning" href="{{url('interessent/'.$vknummer->reserviert_fuer_Interessent->id)}}">{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                                <a class="dropdown-item btn btn-success" href="{{url('vknummern/'.$vknummer->id.'/vergeben')}}">Nummer vergeben</a>
                                                <a class="dropdown-item btn btn-danger deleteReservierung" href="#" data-id="{{$vknummer->id}}" data-name="{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}">Reservierung aufheben</a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <div class="dropdown-header">letzte Verkäufer:</div>
                                            @foreach( $vknummer->bisherigeVerkaeufer->take(5) AS $verkaeufer)
                                                <li class="dropdown-item ">
                                                    <a class="small text-black-50" href="{{url('interessent/'.$verkaeufer->vergeben_an_Interessent->id)}}">{{$verkaeufer->vergeben_an_Interessent->vorname}} {{$verkaeufer->vergeben_an_Interessent->nachname}}</a>
                                                </li>
                                            @endforeach
                                        <div class="dropdown-divider"></div>

                                </div>
                            </div>
                        @endforeach
                </div>
                <div class="row ">
                        @foreach($vknummern->where('vknummer', ">=", 600)->where('vknummer', "<", 700)->all() AS $vknummer)
                            <div class="dropdown m-1 @if ($vknummer->vergeben_an_Interessent != NULL) @elseif ($vknummer->reserviert_fuer_Interessent != NULL) warning @endif"  style="width: 60px;">
                                <button type="button" class="btn btn-sm dropdown-toggle @if ($vknummer->vergeben_an != NULL) btn-success @elseif ($vknummer->reserviert_fuer != NULL) btn-warning @endif" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    {{$vknummer->vknummer}}
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1" >
                                        @if ($vknummer->vergeben_an_Interessent != NULL and is_object($vknummer->vergeben_an_Interessent))
                                            <div class="dropdown-header">vergeben an:</div>
                                                <a class="dropdown-item text-success" href="{{url('interessent/'.$vknummer->vergeben_an_Interessent->id)}}">{{$vknummer->vergeben_an_Interessent->vorname}} {{$vknummer->vergeben_an_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                        @elseif($vknummer->reserviert_fuer_Interessent != NULL and is_object($vknummer->reserviert_fuer_Interessent))
                                            <div class="dropdown-header">reserviert für:</div>
                                                <a class="dropdown-item text-warning" href="{{url('interessent/'.$vknummer->reserviert_fuer_Interessent->id)}}">{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}</a>
                                            <div class="dropdown-divider"></div>
                                                <a class="dropdown-item btn btn-success" href="{{url('vknummern/'.$vknummer->id.'/vergeben')}}">Nummer vergeben</a>
                                                <a class="dropdown-item btn btn-danger deleteReservierung" href="#" data-id="{{$vknummer->id}}" data-name="{{$vknummer->reserviert_fuer_Interessent->vorname}} {{$vknummer->reserviert_fuer_Interessent->nachname}}">Reservierung aufheben</a>
                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <div class="dropdown-header">letzte Verkäufer:</div>
                                            @foreach( $vknummer->bisherigeVerkaeufer->take(5) AS $verkaeufer)
                                                <li class="dropdown-item ">
                                                    <a class="small text-black-50" href="{{url('interessent/'.$verkaeufer->vergeben_an_Interessent->id)}}">{{$verkaeufer->vergeben_an_Interessent->vorname}} {{$verkaeufer->vergeben_an_Interessent->nachname}}</a>
                                                </li>
                                            @endforeach
                                        <div class="dropdown-divider"></div>

                                </div>
                            </div>
                        @endforeach
                </div>
            </div>
        </section>
    </div>
@stop

@section('js')
    <script src="{{asset('js/lib/bootstrap-sweetalert/sweetalert.js')}}"></script>

    <script>
        function alert(id, name){
            swal({
                title: 'Reservierung wirklich aufgeheben?',
                text: "Reserviert für "+name,
                type: 'warning',
                showCancelButton: true,
                confirmButtonClass: 'btn btn-danger',
                cancelButtonClass: 'btn btn-primary',
                confirmButtonText: 'Ja, aufheben!'
            }, function (isConfirmed) {
                console.log(isConfirmed);
                console.log(id);
                if (isConfirmed){
                    var url = "{{url('vknummer/:id/reservierungAufheben')}}";
                    url = url.replace(':id', id);

                    window.location.replace(url);
                }
            });
        }


        $('.deleteReservierung').on('click', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');
            alert(id, name);
        });



    </script>
@stop
@section('css')
    <link rel="stylesheet" href="{{asset('css/lib/bootstrap-sweetalert/sweetalert.css')}}">
@stop