@extends('layouts.app')

@section('js')
    <script>
        @if (!$errors->any())
            $(document).ready(function() {
                $('.hide').hide();
            });
        @else
        $('#showDates').hide();
        @endif



        $('#editBtn').click(function() {
            $('#form').toggle();
            $('#saveBtn').toggle();
            $('#showDates').toggle();
        });
    </script>


@stop

@section('content')
    <div class="container-fluid">
        <header class="section-header">
            <div class="tbl">
                <div class="tbl-row">
                    <div class="tbl-cell">
                        <h2>Grunddaten</h2>
                    </div>
                </div>
            </div>
        </header>

        <div class="row">
            <div class="col-5">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Daten der Klamottenbörse
                        <span class="pull-right">
                            <a class="btn btn-warning btn-sm" id="editBtn">
                                 <i class="font-icon-pencil"></i>
                            </a>

                        </span>
                    </div>
                        <div id="showDates" class="card-body ">
                            <div class="row">
                                <div class="col">
                                    Datum der Klamottenbörse:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->datum->format('d.m.Y') ?: ""}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Anmeldung:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->anmeldung->format('d.m.Y') ?: ""}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Anmeldung Kinderhaus:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->anmeldungKinderhaus->format('d.m.Y') ?: ""}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Anlieferung:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->anlieferung_von ?: ""}} - {{$klamottenboerse->anlieferung_bis ?: ""}} Uhr
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Abholung:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->abholung_von ?: ""}} - {{$klamottenboerse->abholung_bis ?: ""}} Uhr
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col">
                                    max. Teile:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->maxTeile ?: ""}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    automatische Anmeldemail?
                                </div>
                                <div class="col">
                                    {{($klamottenboerse->sendInvitation == 1)? 'ja' : "nein"}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Erinnerungsmail für Verkäufer:
                                </div>
                                <div class="col">
                                    {{($klamottenboerse->sendErinnerung > 0) ? $klamottenboerse->sendErinnerung : "0"}} Tage (0 = keine Erinnerung)
                                </div>
                            </div>

                        </div>
                        <div class="card-body hide" id="form">
                            <form method="post" action="{{url('klamottenboerse/'.$klamottenboerse->id)}}" id="KlamottenboersenForm">
                            {{method_field('PUT')}}
                            {{csrf_field()}}
                            <div class="form-group row @if ($errors->has('datum')) form-group-error @endif">
                                    <label class="form-label" for="datum">Datum der Klamottenbörse:</label>
                                    <input type="date" class="form-control" name="datum" id="datum"  placeholder="__.__.____" value="{{$klamottenboerse->datum->format('Y-m-d') ?: ""}}"  @if ($klamottenboerse->anmeldungKinderhaus < \Carbon\Carbon::now()) readonly @endif>
                                @if ($errors->has('datum'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('datum') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                            <div class="form-group row @if ($errors->has('anmeldung')) form-group-error @endif">
                                <label class="form-label" for="datum">Anmeldung:</label>
                                <input type="date" class="form-control" name="anmeldung" id="anmeldung" placeholder="__.__.____" value="{{$klamottenboerse->anmeldung->format('Y-m-d') ?: ""}}" @if ($klamottenboerse->anmeldung < \Carbon\Carbon::now()) readonly @endif>
                                @if ($errors->has('anmeldung'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('anmeldung') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                            <div class="form-group row @if ($errors->has('anmeldungKinderhaus')) form-group-error @endif">
                                <label class="form-label" for="datum">Anmeldung für das Kinderhaus:</label>
                                <input type="date" class="form-control" name="anmeldungKinderhaus" id="anmeldungKinderhaus" placeholder="__.__.____" value="{{$klamottenboerse->anmeldungKinderhaus->format('Y-m-d') ?: ""}}" @if ($klamottenboerse->anmeldungKinderhaus < \Carbon\Carbon::now()) readonly @endif>
                                @if ($errors->has('anmeldungKinderhaus'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('anmeldungKinderhaus') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>

                            <div class="form-group row @if ($errors->has('anlieferung_von')) form-group-error @endif">
                                <label class="form-label" for="datum"> Anlieferung von:</label>
                                <input type="time" class="form-control" name="anlieferung_von" id="anlieferung_von" placeholder="__.__ Uhr" value="{{$klamottenboerse->anlieferung_von ?: ""}}">
                                @if ($errors->has('anlieferung_von'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('anlieferung_von') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>

                            <div class="form-group row @if ($errors->has('anlieferung_bis')) form-group-error @endif">
                                <label class="form-label" for="datum"> Anlieferung bis:</label>
                                <input type="time" class="form-control" name="anlieferung_bis" id="anlieferung_bis" placeholder="__.__ Uhr" value="{{$klamottenboerse->anlieferung_bis ?: ""}}">
                                @if ($errors->has('anlieferung_bis'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('anlieferung_bis') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>


                            <div class="form-group row @if ($errors->has('abholung_von')) form-group-error @endif">
                                <label class="form-label" for="datum"> Abholung ab:</label>
                                <input type="time" class="form-control" name="abholung_von" id="abholung_von" placeholder="__.__ Uhr" value="{{$klamottenboerse->abholung_von ?: ""}}">
                                @if ($errors->has('abholung_von'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('abholung_von') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>

                            <div class="form-group row @if ($errors->has('abholung_bis')) form-group-error @endif">
                                <label class="form-label" for="datum"> Abholung bis:</label>
                                <input type="time" class="form-control" name="abholung_bis" id="abholung_bis" placeholder="__.__ Uhr" value="{{$klamottenboerse->abholung_bis ?: ""}}">
                                @if ($errors->has('abholung_bis'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('abholung_bis') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>

                            <div class="form-group row @if ($errors->has('maxTeile')) form-group-error @endif">
                                <label class="form-label" for="datum">maximale Teile pro Verkäufer:</label>
                                <input type="number" step="1" class="form-control" name="maxTeile" id="maxTeile" placeholder="__" value="{{$klamottenboerse->maxTeile ?: ""}}">
                                @if ($errors->has('maxTeile'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('maxTeile') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>
                            <div class="form-group row @if ($errors->has('sendInvitation')) form-group-error @endif">
                                <label class="form-label" for="datum">automatische Mail für Anmeldung:</label>
                                <select class="form-control" name="sendInvitation" id="sendInvitation">
                                    <option value="1">Ja</option>
                                    <option value="0">Nein</option>
                                </select>
                                @if ($errors->has('sendInvitation'))
                                    <small class="text-muted">
                                        @foreach ($errors->get('sendInvitation') as $message)
                                            {{ $message }}
                                        @endforeach
                                    </small>
                                @endif
                            </div>

                                <div class="form-group row @if ($errors->has('sendErinnerung')) form-group-error @endif">
                                    <label class="form-label" for="sendErinnerung">Erinnerung für Verkäufer x Tage vor der Klamottenbörse (0=keine):</label>
                                    <input type="number" step="1" min="0" max="14" class="form-control" name="sendErinnerung" id="sendErinnerung" value="{{$klamottenboerse->sendErinnerung ?: 14}}">
                                    @if ($errors->has('sendErinnerung'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('sendErinnerung') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success hide" form="KlamottenboersenForm" id="saveBtn">Speichern</button>
                    </div>
                    <div class="card-footer">
                        <a href="{{url('klamottenboerse/create')}}" class="btn btn-success btn-rounded">neue Klamottenbörse</a>
                    </div>
                </div>


            </div>

            <div class="col-3">
                <div class="card">
                    <div class="card-header">
                        Helfer
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>

            <div class="col-4 col-auto">
                <div class="card">
                    <div class="card-header">
                        vergangene Klamottenbörsen
                    </div>
                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>

    </div>


@stop
