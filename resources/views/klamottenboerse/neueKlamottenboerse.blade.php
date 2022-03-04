@extends('layouts.app')

@section('content')
    <div class="container-fluid">
                    <form class="form-horizontal" role="form" method="POST" action="{{ url('klamottenboerse') }}">
                    {!! csrf_field() !!}

                    <div class="card">
                            <div class="card-header ">
                                Neue Klamottenbörse anlegen
                                <p class="small">Mit dem Anlegen der neuen Klamottenbörse wird die alte abgeschlossen und kann nicht mehr bearbeitet werden.</p>
                            </div>

                  <div class="card-body" id="form">
                                <div class="form-group  @if ($errors->has('datum')) form-group-error @endif">
                                    <label class="form-label" for="datum">Datum der Klamottenbörse:</label>
                                    <input type="date" class="form-control" name="datum" id="datum"  placeholder="__.__.____" value="" required>
                                    @if ($errors->has('datum'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('datum') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>
                                <div class="form-group  @if ($errors->has('anmeldung')) form-group-error @endif">
                                    <label class="form-label" for="datum">Anmeldung:</label>
                                    <input type="date" class="form-control" name="anmeldung" id="anmeldung" placeholder="__.__.____" value="" required>
                                    @if ($errors->has('anmeldung'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('anmeldung') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>
                                <div class="form-group  @if ($errors->has('anmeldungKinderhaus')) form-group-error @endif">
                                    <label class="form-label" for="datum">Anmeldung für das Kinderhaus:</label>
                                    <input type="date" class="form-control" name="anmeldungKinderhaus" id="anmeldungKinderhaus" placeholder="__.__.____" required>
                                    @if ($errors->has('anmeldungKinderhaus'))
                                        <small class="text-muted">
                                            @foreach ($errors->get('anmeldungKinderhaus') as $message)
                                                {{ $message }}
                                            @endforeach
                                        </small>
                                    @endif
                                </div>

                                <div class="form-group  @if ($errors->has('anlieferung_von')) form-group-error @endif">
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

                                <div class="form-group  @if ($errors->has('anlieferung_bis')) form-group-error @endif">
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


                                <div class="form-group  @if ($errors->has('abholung_von')) form-group-error @endif">
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

                                <div class="form-group  @if ($errors->has('abholung_bis')) form-group-error @endif">
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

                                <div class="form-group  @if ($errors->has('maxTeile')) form-group-error @endif">
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

                            </div>
                        <div class="card-footer">
                            <input type="submit" class="btn btn-rounded btn-success btn-block flex-sm-wrap" value="Klamottenbörse abschließen und neue anlegen">
                        </div>
                    </div>
                    </form>
        </div>


@endsection


