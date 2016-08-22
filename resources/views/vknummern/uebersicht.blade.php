@extends('layouts.app')

@section('content')



    <div class="container">
        <div class="row">
            <div class="col-md-9">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-11">
                                Verkäufernummern
                            </div>
                            <div class="col-lg-1">
                                <a href="{{ url('Nummern/new') }}" class="glyphicon glyphicon-plus" title="neue Nummer anlegen"></a>
                            </div>
                        </div>

                    </div>

                    <div class="panel-body">
                        @if(isset($Nummern) and count($Nummern) > 0)
                            <div class="row">

                                @foreach($Nummern AS $Nummer)
                                    <span>
                                 @if($Nummer->vergeben_an == "")

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default">{{ $Nummer->vknummer }}</button>
                                                @if($Nummer->reserviert_fuer != "")
                                                    <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="caret"></span>
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="caret"></span>
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                     </button>
                                                @endif

                                                <ul class="dropdown-menu">

                                                    <li role="separator" class="divider"></li>
                                                    @if($Nummer->reserviert_fuer != "")
                                                        <li class="dropdown-header">reserviert für:</li>
                                                        <li class="dropdown-header">{{$Nummer->reserviert->vorname}} {{$Nummer->reserviert->nachname}}</li>
                                                        <li><a href="{{ url("Nummern/$Nummer->reserviert_fuer/aufheben") }}">aufheben</a></li>
                                                        <li role="separator" class="divider"></li>
                                                        <li><a href="{{ url("Nummern/$Nummer->id/Vergabe") }}">anderweitig vergeben</a></li>
                                                        <li role="separator" class="divider"></li>
                                                    @endif
                                                    <li>
                                                        <a  href="#"
                                                            data-toggle="modal"
                                                            data-target="#Kommentar"
                                                            @if(isset($Nummer->kommentar->kommentar))
                                                            data-kommentar="{{ $Nummer->kommentar->kommentar }}"
                                                            @else
                                                            data-kommentar=""
                                                            @endif
                                                            data-id="{{ $Nummer->id }}">
                                                        Kommentar erstellen
                                                        </a>
                                                    </li>
                                                    <li role="separator" class="divider"></li>
                                                    <li>
                                                        <a  href="#"
                                                            data-toggle="modal"
                                                            data-target="#NummerLoeschen"
                                                            data-title="Nummer löschen"
                                                            data-inhalt="Soll die Nummer {{ $Nummer->vknummer }} wirklich gelöscht werden?"
                                                            data-id="{{ $Nummer->id }}">
                                                        Nummer löschen
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>


                                        @else

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-success">{{ $Nummer->vknummer }}</button>
                                                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li class="dropdown-header">vergeben an:</li>
                                                    <li class="dropdown-header">{{$Nummer->vergeben->vorname}} {{$Nummer->vergeben->nachname}}</li>
                                                    <li><a href="{{ url('Interessent/'.$Nummer->vergeben_an) }}">Vergabe löschen</a></li>
                                                    <li role="separator" class="divider"></li>
                                                    @if($Nummer->reserviert_fuer != "")
                                                        <li class="dropdown-header">reserviert für:</li>
                                                        <li class="dropdown-header">{{$Nummer->reserviert->vorname}} {{$Nummer->reserviert->nachname}}</li>
                                                        <li role="separator" class="divider"></li>
                                                    @endif

                                                    <li>
                                                        <a  href="#"
                                                            data-toggle="modal"
                                                            data-target="#Kommentar"
                                                            @if(isset($Nummer->kommentar->kommentar))
                                                                data-kommentar="{{ $Nummer->kommentar->kommentar }}"
                                                            @else
                                                                data-kommentar=""
                                                            @endif
                                                            data-id="{{ $Nummer->id }}">
                                                        Kommentar erstellen
                                                        </a>
                                                    </li>
                                                    <li role="separator" class="divider"></li>
                                                    <li>
                                                        <a  href="#"
                                                                data-toggle="modal"
                                                                data-target="#NummerLoeschen"
                                                                data-title="Nummer löschen"
                                                                data-inhalt="Soll die Nummer {{ $Nummer->vknummer }} wirklich gelöscht werden?"
                                                                data-id="{{ $Nummer->id }}">
                                                        Nummer löschen
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif

                             </span>
                                @endforeach
                            </div>
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

            <div class="col-md-3">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        Kommentare
                    </div>
                    <div class="panel-body">
                        <div class="list-group">
                            @foreach($Nummern AS $Nummer)
                                @if($Nummer->kommentar != "")

                                    <a  href="#" class="list-group-item"
                                        data-toggle="modal"
                                        data-target="#KommentarLoeschen"
                                        data-title="Kommentar löschen?"
                                        data-inhalt="Soll der Kommentar für die Nummer {{ $Nummer->vknummer }} wirklich gelöscht werden?"
                                        data-id="{{ $Nummer->kommentar->id }}">
                                        <b>{{ $Nummer->vknummer }} </b> - {{ $Nummer->kommentar->kommentar }}
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="panel-footer">

                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="NummerLoeschen" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p id="modal-inhalt"></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="FORMNummerLoeschen" action="{{ url('Nummern/NummerLoeschen')}}">
                        {{csrf_field()}}
                        <input type="hidden" name="id" id="modal-id" >
                        <input type="hidden" name="_method" value="DELETE">
                    </form>
                    <button type="submit" form="FORMNummerLoeschen" class="btn btn-danger">Nummer löschen</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="KommentarLoeschen" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <p id="modal-inhalt"></p>
                </div>
                <div class="modal-footer">
                    <form method="POST" id="FORMKommentarLoeschen" action="{{ url('Nummern/Kommentar/Loeschen')}}">
                        {{csrf_field()}}
                        <input type="hidden" name="id" id="modal-id" >
                        <input type="hidden" name="_method" value="DELETE">
                    </form>
                    <button type="submit" form="FORMKommentarLoeschen" class="btn btn-danger">Kommentar löschen</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="Kommentar" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title">Kommentar erstellen</h4>
                </div>
                <div class="modal-body">
                    <form method="POST" id="KommentarErstellen" action="{{ url('Nummern/Kommentar/store')}}" >
                            {{csrf_field()}}
                            <input type="hidden" name="vknummer" id="modal-id" >
                        <div class="form-group">
                            <textarea name="kommentar" class="form-control" rows="5" id="modal-kommentar" autofocus> </textarea>
                        </div>

                    </form>

                </div>
                <div class="modal-footer">

                    <button type="submit" form="KommentarErstellen" class="btn btn-success">Kommentar speichern</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">

        $('#KommentarLoeschen').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var title = button.data('title')
            var inhalt = button.data('inhalt')
            var id = button.data('id')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-inhalt').text(inhalt)
            modal.find('#modal-id').val(id)
        })


        $('#NummerLoeschen').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var title = button.data('title')
            var inhalt = button.data('inhalt')
            var id = button.data('id')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-inhalt').text(inhalt)
            modal.find('#modal-id').val(id)
        })


            $('#Kommentar').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget) // Button that triggered the modal
                var id = button.data('id')
                var kommentar = button.data('kommentar')


                // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                var modal = $(this)
                modal.find('#modal-id').val(id)
                modal.find('textarea').val(kommentar)
            })

        $('.modal').on('shown.bs.modal', function() {
            $(this).find('[autofocus]').focus();
        });

</script>
@endsection