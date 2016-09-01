@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-10">
                <div class="row">
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p>Informationen - {{ $Interessent->vorname }} {{ $Interessent->nachname }}</p>
                                    </div>

                                    <div class="col-md-3">
                                        <a class="glyphicon glyphicon-remove-circle pull-right" href="{{url("/deleteInteressent/$Interessent->id")}}"> löschen</a>
                                    </div>

                                    <div class="col-md-3">
                                        <a class="glyphicon glyphicon-menu-left pull-right" href="{{ url("/Ueberblick")  }}"> zurück</a>
                                    </div>


                                 </div>


                            </div>
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        Anrede:
                                    </div>
                                    <div class="col-md-6">
                                        <p>

                                            <a href="x" class="pUpdate" id="anrede" data-type="select" data-value="{{ $Interessent->anrede }}" data-pk="{{ $Interessent->id }}" data-title="Anrede bearbeiten">
                                            {{ $Interessent->anrede }}
                                            </a>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        Name:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="x" class="pUpdate" id="nachname" data-type="text" data-pk="{{ $Interessent->id }}" data-title="Nachname bearbeiten">
                                            {{ $Interessent->nachname }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Vorame:
                                    </div>
                                    <div class="col-md-6">
                                        <p>
                                            <a href="x" class="pUpdate" id="vorname" data-type="text" data-pk="{{ $Interessent->id }}" data-title="Vorname bearbeiten">
                                                {{ $Interessent->vorname }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        E-Mail:
                                    </div>
                                    <div class="col-md-6">
                                       <p>
                                            <a href="x" class="pUpdate" data-name="mail" data-type="text" data-pk="{{ $Interessent->id }}" data-title="E-Mail bearbeiten">
                                                {{ $Interessent->mail }}
                                            </a>
                                       </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Telefon:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                           <p>
                                                <a href="x" class="pUpdate" id="telefon"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Telefon bearbeiten">
                                                    {{ $Interessent->telefon }}
                                                </a>
                                           </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Handy:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                        <p>
                                            <a href="x" class="pUpdate" id="handy"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Handy bearbeiten">
                                                {{ $Interessent->handy }}
                                            </a>
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Anschrift:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                            <p>
                                                <a href="x" class="pUpdate" id="straße"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Straße bearbeiten">
                                                    {{ $Interessent->straße }}
                                                </a>
                                                <a href="x" class="pUpdate" id="hausnummer"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Hausnummer bearbeiten">
                                                    {{ $Interessent->hausnummer }}
                                                </a>
                                                <br />
                                                <a href="x" class="pUpdate" id="plz"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Postleitzahl bearbeiten">
                                                    {{ $Interessent->plz }}
                                                </a>
                                                <a href="x" class="pUpdate" id="ort"  data-type="text" data-pk="{{ $Interessent->id }}" data-title="Ort bearbeiten">
                                                    {{ $Interessent->ort }}
                                                </a>
                                            </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Mitarbeiter:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>
                                            <p>
                                                @if($Interessent->mitarbeiter  == 1)
                                                    <a href="x" class="pUpdate" id="mitarbeiter" data-value="1" data-type="select"  data-pk="{{ $Interessent->id }}" data-title="Ist dies ein Mitarbeiter?">
                                                        ja
                                                    </a>
                                                @else
                                                    <a href="x" class="pUpdate" id="mitarbeiter" data-value="0" data-type="select"  data-pk="{{ $Interessent->id }}" data-title="Ist dies ein Mitarbeiter?">
                                                        nein
                                                    </a>
                                                @endif

                                            </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        Kinderhaus:
                                    </div>
                                    <div class="col-md-6">
                                        <div id="_token" class="hidden" data-token="{{ csrf_token() }}"></div>

                                            @if($Interessent->kinderhaus  == 1)
                                                <a href="x" class="pUpdate" id="kinderhaus"  data-value="1" data-type="select" data-pk="{{ $Interessent->id }}" data-title="Eine Familie aus dem Kinderhaus?">
                                                    ja
                                                </a>
                                            @else
                                                <a href="x" class="pUpdate" id="kinderhaus"  data-value="0" data-type="select" data-pk="{{ $Interessent->id }}" data-title="Eine Familie aus dem Kinderhaus?">
                                                    nein
                                                </a>
                                            @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <p>aktuelle Klamottenbörse</p>
                                    </div>

                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                Reservierte Verkäufernummer:
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($Interessent->vknummern_reserviert->vknummer))
                                                    {{ $Interessent->vknummern_reserviert->vknummer }}
                                                @else
                                                    keine Nummer reserviert
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                            zugeteilte Verkäufernummer:
                                            </div>
                                            <div class="col-md-4">
                                                @if(isset($Interessent->vknummern_vergeben->vknummer))
                                                    {{ $Interessent->vknummern_vergeben->vknummer }}
                                                @else
                                                    keine Nummer vergeben
                                                @endif
                                            </div>
                                        </div>

                                        <div class="panel-footer">
                                            @if(isset($Interessent->vknummern_reserviert->vknummer))
                                                    @if(!isset($Interessent->vknummern_vergeben->vknummer))
                                                        <a href="{{url('Nummern/'.$Interessent->id.'/aufheben')}}" class="btn btn-sm btn-danger">Reservierung aufheben</a>
                                                        <button type="button" class="btn  btn-sm  btn-warning"
                                                                data-toggle="modal"
                                                                data-target="#VergabeBestaetigung"
                                                                data-title="Nummer vergeben"
                                                                data-inhalt="Soll die Verkäufernummer {{ $Interessent->vknummern_reserviert->vknummer }} verbindlich an {{$Interessent->vorname }} {{$Interessent->nachname}} vergeben werden?">
                                                            Nummer <b>{{ $Interessent->vknummern_reserviert->vknummer }}</b> vergeben
                                                        </button>
                                                    @endif
                                            @else
                                                @if(!isset($Interessent->vknummern_vergeben->vknummer))
                                                    <a href="{{url('Nummern/'.$Interessent->id.'/reservieren')}}" class="btn btn-sm btn-success">Nummer reservieren</a>
                                                @endif
                                            @endif

                                            @if(!isset($Interessent->vknummern_vergeben->vknummer) and !isset($Interessent->vknummern_reserviert->vknummer))
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-success">Nummer vergeben</button>
                                                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="caret"></span>
                                                            <span class="sr-only">Toggle Dropdown</span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @if(count($haeufigsteNummer)>0)
                                                                <li class="dropdown-header">häufigste VK-Nummer:</li>
                                                                @foreach($haeufigsteNummer AS $Nummer)
                                                                    <li><a href="#" id="VergabeBestaetigung"
                                                                            data-toggle="modal"
                                                                            data-target="#VergabeBestaetigungIndiv"
                                                                            data-title="Nummer vergeben"
                                                                            data-nummernid="{{ $Nummer -> id }}"
                                                                            data-inhalt="Soll die Verkäufernummer  verbindlich an {{ $Nummer->vknummer }} vergeben werden?">
                                                                            {{ $Nummer->vknummer }}
                                                                    </a> </li>
                                                                @endforeach
                                                            @endif

                                                            @if(isset($letzteNummer))
                                                                <li role="separator" class="divider"></li>
                                                                <li class="dropdown-header">letzte VK-Nummer:</li>
                                                                <li><a href="#" id="VergabeBestaetigung"
                                                                           data-toggle="modal"
                                                                           data-target="#VergabeBestaetigungIndiv"
                                                                           data-title="Nummer vergeben"
                                                                           data-nummernid="{{ $letzteNummer -> id }}"
                                                                           data-inhalt="Soll die Verkäufernummer  verbindlich an {{ $letzteNummer->vknummer }} vergeben werden?">
                                                                            {{ $letzteNummer->vknummer }}
                                                                 </a> </li>
                                                            @endif

                                                                <li role="separator" class="divider"></li>
                                                                <li class="dropdown-header">neue VK-Nummer:</li>
                                                                <li><a href="{{url("Nummern/$Interessent->id/vergeben")}}">neue Nummer vergeben</a></li>
                                                        </ul>
                                                    </div>
                                            @endif
                                            @if(isset($Interessent->vknummern_vergeben->vknummer))
                                                    <button type="button" class="btn  btn-sm  btn-danger"
                                                            data-toggle="modal"
                                                            data-target="#VergabeLoeschen"
                                                            data-title="Vergebene Nummer zurücknehmenn"
                                                            data-inhalt="Soll die vergebene Verkäufernummer {{ $Interessent->vknummern_vergeben->vknummer }} von {{$Interessent->vorname }} {{$Interessent->nachname}} zurückgenommen und der Interessent informiert werden?">
                                                        Nummer <b>{{ $Interessent->vknummern_vergeben->vknummer }}</b> freigeben
                                                    </button>
                                            @endif

                                            @if(!isset($Interessent->vknummern_vergeben->vknummer))
                                                    @if(!isset($Interessent->Warteliste))
                                                        <a href="{{url("Warteliste/$Interessent->id")}}" class="btn  btn-sm  btn-info">
                                                            Auf Warteliste
                                                        </a>
                                                    @else
                                                        <form action="{{url("/Warteliste")}}" method="POST">
                                                            {{csrf_field()}}
                                                            {{method_field('delete')}}
                                                            <input type="hidden" name="id" value="{{$Interessent->Warteliste->id}}">
                                                            <button type="submit" class="btn  btn-sm  btn-info">Warteliste aufheben</button>
                                                        </form>
                                                    @endif
                                             @endif

                                            </div>

                                        </div>
                                    </div>
                                 </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="panel panel-default">
                                <div class="panel-heading">
                                    <p>Historie</p>
                                </div>
                                <div class="panel-body">
                                    @if(count($Interessent->nachrichten) != 0)
                                        <div class="list-group">
                                            @foreach($Interessent->nachrichten AS $nachricht)
                                                <button type="button" class="list-group-item"
                                                        data-toggle="modal"
                                                        data-target="#Nachricht"
                                                        data-inhalt="{!!  $nachricht->nachricht !!}"
                                                        data-betreff="{{ $nachricht->created_at->format('d.m.Y H:i') }} - {{ $nachricht->betreff }}"
                                                        @if($nachricht->pfad !="")
                                                            data-anhang ="Anhang: {!! $nachricht->pfad !!}"

                                                        @else
                                                            data-anhang =""
                                                        @endif
                                                        >
                                                            {{ $nachricht->created_at->format('d.m.Y H:i') }} - {{ $nachricht->betreff }}
                                                </button>
                                            @endforeach
                                                {!! $Interessent->nachrichten->render() !!}
                                        </div>
                                    @else
                                        Bisher wurden keine Nachrichten versandt
                                    @endif

                                </div>
                                <div class="panel-footer">
                                    @if(count($Vorlagen)>0)
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-primary"
                                                    data-toggle="modal"
                                                    data-target="#neueNachricht"
                                                    data-title="Neue Nachricht für {{$Interessent->vorname }} {{$Interessent->nachname}}">
                                                neue Nachricht
                                            </button>                                            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu">
                                               @foreach($Vorlagen AS $Vorlage)
                                                   <li><a href="#"
                                                          data-toggle="modal"
                                                          data-target="#neueNachricht"
                                                          data-title="Neue Nachricht für {{$Interessent->vorname }} {{$Interessent->nachname}}"
                                                          data-betreff="{{$Vorlage->betreff}}"
                                                          data-text="{{$Vorlage->text}}"
                                                       >{{$Vorlage->name}}</a> </li>
                                                   @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-primary"
                                                         data-toggle="modal"
                                                         data-target="#neueNachricht"
                                                         data-title="Neue Nachricht für {{$Interessent->vorname }} {{$Interessent->nachname}}">
                                            neue Nachricht
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="modal fade " id="neueNachricht" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form class="form-horizontal" role="form" method="POST" id="Nachrichtenform" action="{{ url('/Nachricht')}}{{ "/".$Interessent->id}}">
                                    {!! csrf_field() !!}
                                    <input type="hidden" form="Nachrichtenform" class="form-control" name="interessent_id" value="{{ $Interessent->id}}">
                                    <div class="form-group">
                                        <label for="Betreff">Betreff</label>
                                        <input type="input" form="Nachrichtenform" class="form-control" name="betreff" placeholder="Betreff" id="modal-betreff">
                                    </div>
                                    <div class="form-group">
                                        <label for="nachricht">Nachricht</label>
                                        <textarea type="textarea" rows="10" class="form-control" name="nachricht" placeholder="Nachricht für {{$Interessent->vorname }} {{$Interessent->nachname}}" id="modal-text"></textarea>
                                    </div>

                                    @if($Dateien ->count() > 0)
                                        <div class="form-group">
                                            <label for="nachricht">Datei anhängen</label>
                                            <select class="form-control" name="anhang">
                                                <option value=""></option>
                                                @foreach($Dateien AS $Datei)
                                                    <option value="{{ $Datei->pfad }}">{{ $Datei->dateiname }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                    @endif

                                </form>
                            </div>
                            <div class="col-md-4">
                                <b>Individualisierung</b>
                                @include('elements.individualisierung')
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="submit" form="Nachrichtenform" class="btn btn-primary" >Nachricht senden</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="Nachricht" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <pre></pre>
                        <span></span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($Interessent->vknummern_reserviert->vknummer))
            <div class="modal fade" id="VergabeBestaetigung" tabindex="-1" role="dialog">
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
                            <form method="POST" id="vergabe" action="{{ url('Nummern/vergeben')}}">
                                {{csrf_field()}}
                                <input type="hidden" name="NummernID" value="{{$Interessent->vknummern_reserviert->id}}">
                                <input type="hidden" name="InteressentenID" value="{{$Interessent->id}}">
                            </form>
                            <button type="submit" form="vergabe" class="btn btn-success">vergeben</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($Interessent->vknummern_vergeben->vknummer))
            <div class="modal fade" id="VergabeLoeschen" tabindex="-1" role="dialog">
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
                            <form method="POST" id="vergabeLoeschen" action="{{ url('Nummern/vergabeLoeschen')}}">
                                {{csrf_field()}}
                                <input type="hidden" name="NummernID" value="{{$Interessent->vknummern_vergeben->id}}">
                                <input type="hidden" name="InteressentenID" value="{{$Interessent->id}}">
                            </form>
                            <button type="submit" form="vergabeLoeschen" class="btn btn-danger">Vergabe aufheben und Interessent informieren</button>
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="modal fade" id="VergabeBestaetigungIndiv" tabindex="-1" role="dialog">
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
                        <form method="POST" id="vergabe" action="{{ url('Nummern/vergeben')}}">
                            {{csrf_field()}}
                            <input type="hidden" name="NummernID" id="NummernID" value="">
                            <input type="hidden" name="InteressentenID" value="{{$Interessent->id}}">
                        </form>
                        <button type="submit" form="vergabe" class="btn btn-success">vergeben</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

    <script type="text/javascript">

        $('#neueNachricht').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var title = button.data('title') // Extract info from data-* attributes
            var nachricht = button.data('text')
            var betreff = button.data('betreff')
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-text').val(nachricht)
            modal.find('#modal-betreff').val(betreff)

        })

        $('#Nachricht').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var nachricht = button.data('inhalt')

            var anhang = button.data('anhang')

            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(betreff)
            modal.find('.modal-body pre').text(nachricht)
            modal.find('.modal-body span').text(anhang)
        })


       $('#VergabeBestaetigung').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var title = button.data('title')
            var inhalt = button.data('inhalt')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-inhalt').text(inhalt)
        })

        $('#VergabeBestaetigungIndiv').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var title = button.data('title')
            var inhalt = button.data('inhalt')
            var nummer = button.data('nummernid')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-inhalt').text(inhalt)
            modal.find('#NummernID').val(nummer)
        })

        $('#VergabeLoeschen').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            var betreff = button.data('betreff') // Extract info from data-* attributes
            var title = button.data('title')
            var inhalt = button.data('inhalt')


            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)
            modal.find('.modal-title').text(title)
            modal.find('#modal-inhalt').text(inhalt)
        })



        $(function() {
            //edit form style - popup or inline
            $.fn.editable.defaults.mode = 'popup';

            $.fn.editable.defaults.params = function (params) {
                params._token = $("#_token").data("token");
                return params;
            };

            $('#mitarbeiter').editable({
               limit: 1,
                source: [
                    {value: '1', text: 'ja'},
                    {value: '0', text: 'nein'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('#anrede').editable({
                limit: 1,
                source: [
                    {value: 'Familie', text: 'Familie'},
                    {value: 'Herr', text: 'Herr'},
                    {value: 'Frau', text: 'Frau'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('#kinderhaus').editable({
                limit: 1,
                source: [
                    {value: '1', text: 'ja'},
                    {value: '0', text: 'nein'}
                ],
                url: '{{URL::to("/")}}/edit-Interessent',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            });

            $('.pUpdate').editable({
                validate: function (value) {
                    if ($.trim(value) == '')
                        return 'Eingabe wird benötigt.';
                },

                url: '{{URL::to("/")}}/edit-Interessent',
                title: 'Bearbeiten',
                placement: 'top',
                send: 'always',
                ajaxOptions: {
                    dataType: 'json',
                    type: 'put'
                }
            })
        })
    </script>

    @endsection


