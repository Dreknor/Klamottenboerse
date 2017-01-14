@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">

                    <form class="form-horizontal" role="form" method="POST" action="{{ url('Grunddaten/Anlegen') }}">
                    {!! csrf_field() !!}

                    <div class="panel panel-info">
                            <div class="panel-heading">
                                <p> Es muss zunächst eine Klamottenbörse angelegt werden.</p>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        Datum der Klamottenbörse:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="date" required name="datum">
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        Anmeldung der Klamottenbörse:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="date" required name="anmeldung">
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        Anmeldung Kinderhaus:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="date" required name="anmeldungKinderhaus">
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        Anlieferung von:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="time" required name="anlieferung_von">
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        Anlieferung bis:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="time" required name="anlieferung_bis">
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        Abholung von:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="time" required name="abholung_von">
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        Abholung bis:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="time" required name="abholung_bis">
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-5">
                                        maximale Teile je Verkäufer:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="number" step="1" required name="maxTeile">
                                        </p>
                                    </div>
                                </div>

                            </div>
                            <div class="panel-footer">
                                <input type="submit" class="btn btn-success" value="Klamottenbörse anlegen">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


@endsection


