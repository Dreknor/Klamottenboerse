@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <p>Neue Mailvorlage anlegen</p>
                    </div>
                    <div class="panel-body">
                        <form  role="form" method="POST" id="neueVorlage" action="{{ url("Mailvorlagen/new")}}">
                            {{csrf_field()}}
                            <div class="form-group">
                                <label for="Name">Name der Vorlage</label>
                                <input type="text" class="form-control" id="name" placeholder="Name" name="name" value="{!! old('name') !!}" >
                            </div>
                            <div class="form-group">
                                <label for="Betreff">Betreff</label>
                                <input type="text" class="form-control" id="Betreff" placeholder="Betreff" name="betreff" value="{!! old('betreff') !!}">
                            </div>
                            <div class="form-group">
                                <label for="Betreff">Nachrichtentext</label>
                                <textarea type="text" class="form-control" id="Nachricht" placeholder="Hier kommt der Mailtext hin" name="text" rows="8">{!! old('text') !!}</textarea>
                            </div>


                        </form>

                    </div>
                    <div class="panel-footer">
                        <button type="submit" class="btn btn-success" form="neueVorlage">Erstellen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection