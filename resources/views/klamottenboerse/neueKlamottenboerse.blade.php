@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                       <div class="alert alert-danger">
                            ACHTUNG: Mit dem Abschluss der Klamottenbörse und dem Anlegen einer Neuen, können keine Änderungen mehr an der abgeschlossenen Klamottenbörse vorgenommen werden. Dies betrifft sowohl Helfer, als auch die Verkäufernummern.
                       </div>

                    <form class="form-horizontal" role="form" method="POST" action="{{ url('Grunddaten/Anlegen') }}">
                    {!! csrf_field() !!}

                    <div class="panel panel-info">
                            <div class="panel-heading">
                                <p>Für den Abschluss muss das Datum für die nächste Klamottenbörse festgelegt werden.</p>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-5">
                                        Datum der nächsten Klamottenbörse:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <input type="date" required name="datum">
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-footer">
                                <input type="submit" class="btn btn-danger" value="Klamottenbörse abschließen">
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


@endsection


