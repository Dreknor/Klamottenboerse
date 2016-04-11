@extends('layouts.app')

@if( !isset($Gruppe))
    {{ $Gruppe = 'All' }}
@endif

@section('content')

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel panel-default">
                    <div class="btn-group btn-group-justified" role="group" aria-label="...">
                        <a href="{{ url('/Ueberblick/All') }}" class="btn btn-default">
                            Alle
                            <span class="badge">{{ $InteressentenCount }}</span>
                        </a>
                        <a href="{{ url('/Ueberblick/Mitarbeiter') }}" class="btn btn-default">
                            Mitarbeiter
                            <span class="badge">{{ $MitarbeiterCount }}</span>
                        </a>
                        <a href="{{ url('/Ueberblick/Kinderhaus') }}" class="btn btn-default">
                            Kinderhaus
                            <span class="badge">{{ $KinderhausCount }}</span>
                        </a>
                        <a href="#" class="btn btn-default">
                            Verkäufer
                            <span class="badge"></span>
                        </a>
                        <a href="#" class="btn btn-default">
                            Warteliste
                            <span class="badge"></span>
                        </a>
                    </div>
              </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-body">
                    <div class="col-md-8">
                        <p>Aktuelle Gruppe: {{ $Gruppe }}</p>
                    </div>
                    <div class="col-md-4">
                        <div class="pull-right">
                            <form class="navbar-form" role="search" method="post" action="{{action("InteressentenController@search")}}">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Suche" name="SearchString">
                                    <div class="input-group-btn">
                                        <button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
                                    </div>
                                </div>
                                {{ csrf_field() }}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="row">
            <div class="col-md-10 col-md-offset-1">
               <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-md-10">
                                Interessentenübersicht
                            </div>
                            <div class="col-md-1">
                                <a href="{{url("/Ueberblick/$Gruppe/export")}}" class="glyphicon glyphicon-share pull-right" title="Gruppe exportieren"> </a>
                            </div>
                            <div class="col-md-1 ">
                                <a href="{{url("/Ueberblick/$Gruppe/mail")}}"
                                   class="glyphicon glyphicon glyphicon-envelope "
                                   title="E-Mail an Gruppe senden"
                                   data-toggle="modal"
                                   data-target="#neueNachricht"
                                   data-title="Neue Nachricht für {{$Gruppe}}"> </a>
                            </div>
                        </div>

                    </div>

                    <div class="panel-body">
                        <table class="table .table-hover">
                            <thead>
                                <tr>
                                    <th>Nachname</th>
                                    <th>Vorname</th>
                                    <th>Telefon</th>
                                    <th>E-Mail</th>
                                    <th>Anschrift</th>
                                    <th>Kinderhaus</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($entries AS $Interessent)
                                    <tr>
                                        <td>{{ $Interessent->nachname }}</td>
                                        <td>{{ $Interessent->vorname }}</td>
                                        <td>{{ $Interessent->telefon }}</td>
                                        <td>{{ $Interessent->mail }}</td>
                                        <td>{{ $Interessent->straße }} {{ $Interessent->hausnummer }} <br />
                                            {{ $Interessent->plz }}{{ $Interessent->ort }}</td>

                                        @if($Interessent->kinderhaus == 1)
                                            <td>ja</td>
                                        @else
                                            <td>nein</td>
                                        @endif
                                        <td>
                                            <a href="{{url("/Interessent/$Interessent->id")}}">
                                                <span class="glyphicon glyphicon-eye-open"
                                                      data-toggle="tooltip"  data-placement="right" title="Profil von {{ $Interessent->vorname }} {{ $Interessent->nachname }}">

                                                </span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="neueNachricht" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modal-title">Nachricht an Gruppe senden</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" role="form" method="POST" id="Nachrichtenform" action="{{ url("/mail/$Gruppe")}}">
                        {!! csrf_field() !!}
                       <div class="form-group">
                            <label for="Betreff">Betreff</label>
                            <input type="input" form="Nachrichtenform" class="form-control" name="betreff" placeholder="Betreff">
                        </div>
                        <div class="form-group">
                            <label for="nachricht">Nachricht</label>
                            <textarea type="textarea" rows="10" class="form-control" name="nachricht" placeholder="Nachricht für an Gruppe {{ $Gruppe }}"></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="submit" form="Nachrichtenform" class="btn btn-primary" >Nachricht an {{ $Gruppe }} senden</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script type="text/javascript">
        <script type="text/javascript">

                $('#neueNachricht').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget) // Button that triggered the modal
                    var title = button.data('title') // Extract info from data-* attributes
                    var nachricht = button.data('inhalt') // Extract info from data-* attributes
                    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                    var modal = $(this)
                    modal.find('.modal-title').text(title);
                    modal.find('.modal-body pre').text(nachricht)
                })


    </script>
@endsection


