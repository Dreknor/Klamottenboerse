@extends('layouts.app')


@section('content')
    <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Daten der aktuellen Klamottenbörse</p>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        Datum der Klamottenbörse:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="datum" data-type="date" data-value="{{ $Klamottenboerse->datum }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Wann findet die Klamottenbörse statt?">
                                                {{ $Klamottenboerse->datum->format('d.m.Y')  }}
                                            </a>

                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        Anmeldung:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="anmeldung" data-type="date" data-value="{{ $Klamottenboerse->anmeldung }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Anmeldedatum bearbeiten">
                                                @if($Klamottenboerse->anmeldung != null and $Klamottenboerse->anmeldung->year > 1990)
                                                    {{ $Klamottenboerse->anmeldung->format('d.m.Y') }}
                                                @else
                                                    Noch kein Datum angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        Anmeldung für das Kinderhaus:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="anmeldungKinderhaus" data-type="date" data-value="{{ $Klamottenboerse->anmeldungKinderhaus }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Datum der Anmeldung für das Kinderhaus bearbeiten">
                                            @if($Klamottenboerse->anmeldungKinderhaus != null and $Klamottenboerse->anmeldungKinderhaus->year > 1990 )
                                                {{ $Klamottenboerse->anmeldungKinderhaus->format('d.m.Y') }}
                                            @else
                                                Noch kein Datum angegeben
                                                @endif
                                                </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        Anlieferung ab:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="anlieferung_von" data-type="time" data-value="{{ $Klamottenboerse->anlieferung_von }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Anlieferung ab ... Uhr">
                                                @if($Klamottenboerse->anlieferung_von != null )
                                                    {{ $Klamottenboerse->anlieferung_von }} Uhr
                                                @else
                                                    Kein Zeitpunkt angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        Anlieferung bis:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="anlieferung_bis" data-type="time" data-value="{{ $Klamottenboerse->anlieferung_bis }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Anlieferung bis ... Uhr">
                                                @if($Klamottenboerse->anlieferung_bis != null )
                                                    {{ $Klamottenboerse->anlieferung_bis }} Uhr
                                                @else
                                                    Kein Zeitpunkt angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        Abholung ab:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="abholung_von" data-type="time" data-value="{{ $Klamottenboerse->abholung_von }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Anlieferung bis ... Uhr">
                                                @if($Klamottenboerse->abholung_von != null )
                                                    {{ $Klamottenboerse->abholung_von }} Uhr
                                                @else
                                                    Kein Zeitpunkt angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-8">
                                        Abholung bis:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="abholung_bis" data-type="time" data-value="{{ $Klamottenboerse->abholung_bis }}" data-pk="{{ $Klamottenboerse->id }}" data-title="Anlieferung bis ... Uhr">
                                                @if($Klamottenboerse->abholung_bis != null )
                                                    {{ $Klamottenboerse->abholung_bis }} Uhr
                                                @else
                                                    Kein Zeitpunkt angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        maximale Teile pro Verkäufer:
                                    </div>
                                    <div class="col-md-4">
                                        <p>
                                            <a href="x" id="maxTeile" data-type="number" data-value="{{ $Klamottenboerse->maxTeile }}" data-pk="{{ $Klamottenboerse->id }}" data-title="maximale Teile pro Verkäufer">
                                                @if($Klamottenboerse->maxTeile != null )
                                                    {{ $Klamottenboerse->maxTeile }}
                                                @else
                                                   Keine Anzahl angegeben
                                                @endif
                                            </a>
                                        </p>
                                    </div>
                                </div>

                            </div>
                            <div class="panel-footer">
                                <a href="{{ url('Grunddaten/abschliessen') }}" class="btn btn-danger">Klamottenbörse abschließen</a>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <p>Helfer</p>
                            </div>
                            <div class="panel-body">
                                @if(count($Klamottenboerse->helfer) != 0)
                                    <div class="list-group">
                                        @foreach($Klamottenboerse->helfer AS $Helfer)
                                            <button type="button" class="list-group-item"
                                                    data-toggle="modal"
                                                    data-target="#Helfer"
                                                    data-name="{!!  $Helfer->name !!}"
                                                    data-mail="{{ $Helfer->mail }}"
                                                    data-telefon="{{ $Helfer->telefon }}"
                                                    data-bereich="{{ $Helfer->bereich }}"

                                            >
                                                <strong>{{  $Helfer->bereich  }} </strong> - {{  $Helfer->name  }}
                                            </button>
                                        @endforeach
                                        {!! $Klamottenboerse->helfer->render() !!}
                                    </div>
                                @else
                                    Bisher wurden keine Helfer erfasst
                                @endif

                            </div>
                            <div class="panel-footer">
                                <button type="button" class="btn btn-success"
                                        data-toggle="modal"
                                        data-target="#neuerHelfer"
                                        data-title="Neuen Helfer anlegen">
                                    neuer Helfer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Helfer" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <strong>E-Mail:</strong> <p id="mail"></p> <br>
                    <strong>Telefon:</strong> <p id="telefon"></p><br>
                    <strong>Bereich:</strong> <p id="bereich"></p><br>

                </div>
                <div class="modal-footer">
                    @if(count($Klamottenboerse->helfer) != 0)
                        <form action="{!! url('Grunddaten/'.$Helfer->id.'/delete') !!}" method="post">
                            {!! csrf_field() !!}
                            {!! method_field('delete') !!}
                             <button type="submit" class="btn btn-default btn-danger">
                                <span class="glyphicon glyphicon-trash"></span> Helfer löschen
                            </button>

                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="neuerHelfer" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" role="form" method="POST" id="Helferform" action="{{ url('Grunddaten/Helfer/store')}}">
                        {!! csrf_field() !!}
                        <input type="hidden" name="klamottenboerse_id" value="{{$Klamottenboerse->id}}">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="input" form="Helferform" class="form-control" name="name" placeholder="Name des Helfer">
                        </div>
                        <div class="form-group">
                            <label for="mail">E-Mail</label>
                            <input type="input" form="Helferform" class="form-control" name="mail" placeholder="E-Mail des Helfer">
                        </div>

                        <div class="form-group">
                            <label for="telefon">Telefon</label>
                            <input type="input" form="Helferform" class="form-control" name="telefon" placeholder="Telefon des Helfer">
                        </div>

                        <div class="form-group">
                            <label for="bereich">Bereich</label>
                            <input type="input" form="Helferform" class="form-control" name="bereich" placeholder="Bereich wo geholfen wird">
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="Helferform" class="btn btn-success" >Helfer anlegen</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">

        $('#neuerHelfer').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var title = button.data('title') // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title);

        })

        $('#Helfer').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var name = button.data('name') // Extract info from data-* attributes
            var mail = button.data('mail')
            var telefon = button.data('telefon')
            var bereich = button.data('bereich')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(name)
            modal.find('.modal-body #mail').text(mail)
            modal.find('.modal-body #telefon').text(telefon)
            modal.find('.modal-body #bereich').text(bereich)

        })

        $(function() {
            //edit form style - popup or inline
            $.fn.editable.defaults.mode = 'popup';

            $.fn.editable.defaults.params = function (params) {
                params._token = $("#_token").data("token");
                return params;
            };

            $('#datum').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                viewformat: 'dd.mm.yyyy',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#anmeldung').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                viewformat: 'dd.mm.yyyy',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#anmeldungKinderhaus').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                viewformat: 'dd.mm.yyyy',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#anlieferung_von').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                viewformat: 'hh:ii',
                format: 'hh:ii',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#anlieferung_bis').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                format: 'hh:ii',
                viewformat: 'hh:ii',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });


            $('#abholung_bis').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                format: 'yhh:ii',
                viewformat: 'hh:ii',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#abholung_von').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                format: 'hh:ii',
                viewformat: 'hh:ii',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

            $('#maxTeile').editable({
                url: '{{URL::to("/")}}/edit-Klamottenboerse',
                title: 'Bearbeiten',
                placement: 'down',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'}
            });

        })
    </script>


@endsection


