@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-10">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Neue Datei hochladen
                            </div>
                            <div class="panel-body">


                                <form class="form-horizontal" role="form" method="POST" id="Uploadform" enctype="multipart/form-data" action="Dateien/add">
                                    {!! csrf_field() !!}
                                    {!! method_field('PUT') !!}
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="file" name="file">
                                            </div>
                                            <div class="col-md-9">
                                               <input type="submit" class="btn btn-default" value="Datei hochladen">
                                            </div>
                                        </div>
                                    </div>


                                </form>




                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="row">
                    <div class="col-md-10">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                               Dateiverwaltung
                            </div>
                            <div class="panel-body">

                                @if(count($Dateien) > 0)
                                    <form class="form-horizontal" role="form" method="POST" id="Dateiform" action="">
                                        {!! csrf_field() !!}
                                        {!! method_field('DELETE') !!}
                                        <ul class="list-group">
                                            @foreach($Dateien AS $Datei)
                                                <li class="list-group-item clearfix" data-toggle="tooltip" data-placement="right" title="{{ $Datei->dateibeschreibung }}">
                                                    <a href="{{ url('/Dateien/'.$Datei->id) }}" target="_blank">{{ $Datei->dateiname }}</a>
                                                    <button type="submit" id="delete-task" formaction="Dateien/{{ $Datei->id }}" class="btn btn-danger glyphicon glyphicon-trash pull-right">
                                                        Löschen
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </form>
                                @else
                                    Es sind keine Dateien vorhanden
                                @endif



                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

<script type="text/javascript">
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>
@endsection


