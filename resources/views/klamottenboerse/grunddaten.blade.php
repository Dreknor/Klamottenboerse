@extends('layouts.app')

@section('js')
    <script src='{{asset('js/tinymce/tinymce.min.js')}}'></script>
    <script src='{{asset('js/tinymce/langs/de.js')}}'></script>
<script>
    tinymce.init({
        plugins: "autolink, lists, textcolor",
        selector: '#belehrung',
        toolbar:'bold, italic, underline, strikethrough, alignleft, aligncenter, alignright, alignjustify, styleselect, formatselect, fontselect, fontsizeselect, forecolor, backcolor, bullist, numlist, outdent, indent, undo, redo, removeformat',
        menubar: false,
        height: 300,
        table_default_attributes: {
            border: '0'
        }
    });
</script>
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
            <div class="col-8">
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

                            <div class="row mt-2">
                                <div class="col">
                                    Verkäufer können online Verkaufsergebnis einsehen?:
                                </div>
                                <div class="col">
                                    {{($klamottenboerse->ergebnis_freigabe == 1)? 'ja' : "nein"}}
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col">
                                    Ort:
                                </div>
                                <div class="col">
                                    {{$klamottenboerse->ort}}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Adresse:
                                </div>
                                <div class="col">
                                    <a href="https://www.google.com/maps/place/{{urlencode($klamottenboerse->adresse)}}" target="_blank">
                                        {{$klamottenboerse->adresse}}
                                    </a>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    Belehrung:
                                </div>
                                <div class="col">
                                    {!! $klamottenboerse->belehrung !!}
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

                                <div class="form-group row @if ($errors->has('ergebnis_freigabe')) form-group-error @endif">
                                    <label class="form-label" for="ergebnis_freigabe">Ergebnisfreigabe:</label>
                                    <select class="form-control" name="ergebnis_freigabe" id="ergebnis_freigabe">
                                        <option value="1" @if($klamottenboerse->ergebnis_freigabe == 1) selected @endif>Ja</option>
                                        <option value="0" @if($klamottenboerse->ergebnis_freigabe == 0) selected @endif>Nein</option>
                                    </select>
                                    @if ($errors->has('ergebnis_freigabe'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('ergebnis_freigabe') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>

                                <div class="form-group row @if ($errors->has('live_verkaufsansicht_freigabe')) form-group-error @endif">
                                    <label class="form-label" for="live_verkaufsansicht_freigabe">Live-Verkaufsansicht für Verkäufer:</label>
                                    <select class="form-control" name="live_verkaufsansicht_freigabe" id="live_verkaufsansicht_freigabe">
                                        <option value="1" @if($klamottenboerse->live_verkaufsansicht_freigabe == 1) selected @endif>Ja</option>
                                        <option value="0" @if($klamottenboerse->live_verkaufsansicht_freigabe == 0) selected @endif>Nein</option>
                                    </select>
                                    @if ($errors->has('live_verkaufsansicht_freigabe'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('live_verkaufsansicht_freigabe') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>


                                <div class="form-group row @if ($errors->has('ort')) form-group-error @endif">
                                    <label class="form-label" for="ort">Ort:</label>
                                    <input type="text" class="form-control" name="ort" id="ort" value="{{$klamottenboerse->ort ?: ""}}">
                                    @if ($errors->has('ort'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('ort') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>

                                <div class="form-group row @if ($errors->has('adresse')) form-group-error @endif">
                                    <label class="form-label" for="adresse">Adresse:</label>
                                    <input type="text" class="form-control" name="adresse" id="adresse" value="{{$klamottenboerse->adresse ?: ""}}">
                                    @if ($errors->has('adresse'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('adresse') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>


                                <div class="form-group row @if ($errors->has('belehrung')) form-group-error @endif">
                                    <label class="form-label" for="belehrung">Belehrung:</label>
                                    <textarea class="form-control" name="belehrung" id="belehrung">
                                        {!! $klamottenboerse->belehrung !!}
                                    </textarea>
                                    @if ($errors->has('belehrung'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('belehrung') as $message)
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
        </div>

    </div>


@stop
