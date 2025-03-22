@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="card-columns">
            <div class="card">
                <div class="card-header">
                    <span  id="headerName">{{$interessent->vorname}} {{$interessent->nachname}}</span>
                    <span class="pull-right">
                            <a class="btn btn-warning btn-sm" id="editBtn">
                                 <i class="font-icon-pencil"></i>
                            </a>
                    </span>
                            @if ($interessent->mitarbeiter == 'ja')
                                <span class="pull-right">
                                    <div class="col"  id="loginText">
                                        @if (!is_null($interessent->user) and $interessent->user->verwatung == 1)
                                                <a class="btn btn-danger btn-sm"  href="{{url('/interessenten/'.$interessent->id.'/deleteUserAccount')}}" title="Login löschen">
                                                <i class="fa fa-lock" aria-hidden="true"></i>
                                            </a>
                                            @else
                                                <a class="btn btn-info btn-sm" href="{{url('/interessenten/'.$interessent->id.'/addUserAccount')}}" title="Login erstellen">
                                                <i class="fa fa-key" aria-hidden="true"></i>
                                             </a>
                                            @endif
                                    </div>
                                </span>
                                @if (!is_null($interessent->user) and $interessent->user->kasse == 1)
                                    <span class="pull-right">

                                            <a class="btn btn-danger btn-sm"  href="{{url('/interessenten/'.$interessent->id.'/removeKassenZugang')}}" title="Kassenzugang löschen">
                                            <i class="fa-solid fa-cash-register"></i>
                                        </a>
                                        @else
                                            <a class="btn btn-secondary btn-sm" href="{{url('/interessenten/'.$interessent->id.'/createKassenZugang')}}" title="Kassenlogin erstellen">
                                           <i class="fa-solid fa-cash-register"></i>
                                         </a>
                                    </span>
                                @endif
                            @endif
                </div>
                <div id="daten" class="card-body ">
                    <div class="row mt-2">
                        <div class="col">
                            Anrede
                        </div>
                        <div class="col" id="anredeText">
                            {{$interessent->anrede}}
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            Vorname
                        </div>
                        <div class="col" id="vornameText">
                            {{$interessent->vorname}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            Nachname
                        </div>
                        <div class="col" id="nachnameText">
                            {{$interessent->nachname}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            Telefon:
                        </div>
                        <div class="col" id="telefonText">
                            {{$interessent->telefon}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            Handy:
                        </div>
                        <div class="col" id="handyText">
                            {{$interessent->handy}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            E-Mail:
                        </div>
                        <div class="col"  id="mailText">
                            {{$interessent->mail}}
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col">
                            Ergebnis:
                        </div>
                        <div class="col" id="handyText">
                            <a href="{{url('ergebnis/'.$interessent->uuid)}}">{{$interessent->uuid}}</a>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col" >
                            Kinderhaus
                        </div>
                        <div class="col" id="kinderhausText">
                            {{$interessent->kinderhaus}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            Mitarbeiter
                        </div>
                        <div class="col"  id="mitarbeiterText">
                            {{$interessent->mitarbeiter}}
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col">
                            Verwaltungslogin
                        </div>
                        @if ($interessent->mitarbeiter == 'ja')
                            <div class="col"  id="loginText">
                                @if (!is_null($interessent->user))
                                    Zugang vorhanden
                                @else
                                    Zugang ist nicht vorhanden
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body hide" id="form">
                    <form method="post" action="{{url('interessenten/'.$interessent->id)}}" id="InteressentenForm">
                        {{method_field('PUT')}}
                        {{csrf_field()}}
                        <div class="form-group row @if ($errors->has('anrede')) form-group-error @endif">
                            <label class="form-label" for="anrede">Anrede:</label>
                            <select class="custom-select" name="anrede" tabindex="-1" aria-hidden="true" id="anrede">
                                <option @if ($interessent->anrede == "Herr")  selected @endif>Herr</option>
                                <option @if ($interessent->anrede == "Frau")  selected @endif>Frau</option>
                                <option @if ($interessent->anrede == "Familie")  selected @endif>Familie</option>

                            </select>
                        </div>
                        <div class="form-group row @if ($errors->has('vorname')) form-group-error @endif">
                            <label class="form-label" for="vorname">Vorname:</label>
                            <input type="text" class="form-control" name="vorname" id="vorname"   value="{{$interessent->vorname ?: ""}}" >
                            @if ($errors->has('vorname'))
                                <small class="text-muted">
                                    @foreach ($errors->get('vorname') as $message)
                                        {{ $message }}
                                    @endforeach
                                </small>
                            @endif
                        </div>
                        <div class="form-group row @if ($errors->has('nachname')) form-group-error @endif">
                            <label class="form-label" for="datum">Nachname:</label>
                            <input type="text" class="form-control" name="nachname" id="nachname"  value="{{$interessent->nachname ?: ""}}">
                            @if ($errors->has('nachname'))
                                <small class="text-muted">
                                    @foreach ($errors->get('nachname') as $message)
                                        {{ $message }}
                                    @endforeach
                                </small>
                            @endif
                        </div>
                        <div class="form-group row @if ($errors->has('mail')) form-group-error @endif">
                            <label class="form-label" for="datum">E-Mail:</label>
                            <input type="text" class="form-control" name="mail" id="mail"  value="{{$interessent->mail ?: ""}}" >
                            @if ($errors->has('mail'))
                                <small class="text-muted">
                                    @foreach ($errors->get('mail') as $message)
                                        {{ $message }}
                                    @endforeach
                                </small>
                            @endif
                        </div>

                        <div class="form-group row @if ($errors->has('telefon')) form-group-error @endif">
                            <label class="form-label" for="datum">Telefon:</label>
                            <input type="text" class="form-control" name="telefon" id="telefon"  value="{{$interessent->telefon ?: ""}}" >
                            @if ($errors->has('telefon'))
                                <small class="text-muted">
                                    @foreach ($errors->get('telefon') as $message)
                                        {{ $message }}
                                    @endforeach
                                </small>
                            @endif
                        </div>

                        <div class="form-group row @if ($errors->has('handy')) form-group-error @endif">
                            <label class="form-label" for="datum">Handy:</label>
                            <input type="text" class="form-control" name="handy" id="handy"  value="{{$interessent->handy ?: ""}}" >
                            @if ($errors->has('handy'))
                                <small class="text-muted">
                                    @foreach ($errors->get('handy') as $message)
                                        {{ $message }}
                                    @endforeach
                                </small>
                            @endif
                        </div>


                        <div class="row">
                            <div class="form-group col">
                                <div class="checkbox-toggle">
                                    <input type="checkbox" id="mitarbeiter" name="mitarbeiter" @if ($interessent->mitarbeiter == "ja") checked @endif>
                                    <label for="mitarbeiter">Mitarbeiter</label>
                                </div>
                            </div>

                            <div class="form-group col">
                                <div class="checkbox-toggle">
                                    <input type="checkbox" id="kinderhaus" name="kinderhaus" @if ($interessent->kinderhaus == "ja") checked @endif>
                                    <label for="kinderhaus">Kinderhaus</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                <div class="card-footer hide" id="footer">
                    <button type="submit" class="btn btn-success " form="InteressentenForm" id="saveBtnForm">Speichern</button>

                    <p class="btn btn-danger " id="deleteInteressentButton">Interessent löschen</p>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    aktuelle Klamottenbörse
                </div>
                <div class="card-body">
                    <div class="row">
                        @if ($interessent->vknummer_reserviert)
                            <div class="col-md-6">
                                <b>
                                    reservierte Nummer:
                                </b>
                            </div>
                            <div class="col-md-6">
                                <button class="btn  btn-warning-outline">
                                    {{$interessent->vknummer_reserviert->vknummer}}
                                </button>
                            </div>
                        @else
                            <div class="alert alert-warning  col-12">
                                Keine Nummer reserviert
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if ($interessent->vknummern_vergeben)
                            <div class="col-md-6">
                                <b>
                                    vergebene Nummer:
                                </b>
                            </div>
                            <div class="col-md-6">
                                <button class="btn  btn-primary-outline">
                                    {{$interessent->vknummern_vergeben->vknummer}}
                                </button>
                            </div>
                        @else
                            <div class="alert alert-primary  col-12">
                                Keine Nummer vergeben
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer">
                    @if ($interessent->vknummern_vergeben)
                        <a class="btn btn-danger btn-sm btn-block" href="#removeVergabeModal" role="button" data-toggle="modal">Nummer <b>{{$interessent->vknummern_vergeben->vknummer}}</b> freigeben</a>
                        <a class="btn  btn-sm btn-block" target="_blank" href="{{url('listen/belehrung/'.$interessent->vknummern_vergeben->vknummer)}}">Belehrung drucken</a>

                    @elseif (!($interessent->vknummern_vergeben) and $interessent->vknummer_reserviert)
                        <div class="btn-group btn-group-sm btn-block">
                            <a class="btn btn-success btn-sm btn-block @if ($interessent->vknummer_reserviert->vergeben_an != "") disabled @endif" href="{{url('vknummer/'.$interessent->vknummer_reserviert->id.'/vergeben')}}">Nummer <b>{{$interessent->vknummer_reserviert->vknummer}}</b> @if ($interessent->vknummer_reserviert->vergeben_an != "") bereits @endif vergeben </a>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                <a class="dropdown-item" href="{{url('vknummer/'.$interessent->id.'/Nummervergeben')}}">andere Nummer vergeben</a>
                            </ul>
                        </div>
                        <a class="btn btn-warning btn-sm btn-block" href="{{url('vknummer/'.$interessent->vknummer_reserviert->id.'/reservierungAufheben')}}">Reservierung <b>{{$interessent->vknummer_reserviert->vknummer}}</b> aufheben</a>
                        @if ($interessent->warteliste)

                            <form method="post" action="{{url('warteliste/'.$interessent->id)}}" class="form-horizontal mt-2 mb-2">
                                @csrf
                                @method('delete')
                                <button class="btn btn-info-outline btn-sm btn-block" type="submit">Warteliste aufheben</button>
                            </form>

                        @else
                            <a class="btn btn-info-outline btn-sm btn-block" href="{{url('warteliste/'.$interessent->id."/set")}}">auf Warteliste</a>
                        @endif
                    @else
                        <a class="btn btn-warning-outline btn-sm btn-block" href="{{url('vknummer/'.$interessent->id.'/reservierung')}}">Nummer reservieren</a>
                        @if ($interessent->warteliste)

                            <form method="post" action="{{url('warteliste/'.$interessent->id)}}" class="form-horizontal mt-2 mb-2">
                                @csrf
                                @method('delete')
                                <button class="btn btn-info-outline btn-sm btn-block" type="submit">Warteliste aufheben</button>
                            </form>

                        @else
                            <a class="btn btn-info-outline btn-sm btn-block" href="{{url('warteliste/'.$interessent->id."/set")}}">auf Warteliste</a>
                        @endif
                        <div class="btn-group btn-group-sm btn-block">
                            <a class="btn btn-success  btn-block" href="{{url('vknummer/'.$interessent->id.'/Nummervergeben')}}">Nummer vergeben</a>
                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu">
                                @if(count($interessent->bisherige_vknummen)>0)
                                    <h6 class="dropdown-header">letzte VK-Nummer:</h6>
                                    <a class=" dropdown-item @if ($letzteVKnummer->aktuelleKlamottenboerse->vergeben_an == "" and $letzteVKnummer->aktuelleKlamottenboerse->reserviert_fuer == "" ) text-info VKNummer"
                                       data-nummer="{{$letzteVKnummer->aktuelleKlamottenboerse->vknummer}}" data-id="{{$letzteVKnummer->aktuelleKlamottenboerse->id}} @else text-danger @endif">
                                        {{$letzteVKnummer->aktuelleKlamottenboerse->vknummer}}
                                        @if ($letzteVKnummer->aktuelleKlamottenboerse->vergeben_an != "")
                                            - ist vergeben
                                        @elseif ($letzteVKnummer->aktuelleKlamottenboerse->reserviert_fuer != "")
                                            - ist reserviert
                                        @else
                                            - vergeben und informieren
                                        @endif
                                    </a>
                                    <h6 role="separator" class="dropdown-divider"></h6>
                                    <h6 class="dropdown-header">häufigste VK-Nummer:</h6>

                                    @foreach($haeufigsteVKnummer AS $vknummer =>$Nummer)
                                        <a class=" dropdown-item @if ($Nummer->first()->aktuelleKlamottenboerse->vergeben_an == "" and $Nummer->first()->aktuelleKlamottenboerse->reserviert_fuer == "" ) text-info VKNummer"
                                           data-nummer="{{$Nummer->first()->aktuelleKlamottenboerse->vknummer}}" data-id="{{$Nummer->first()->aktuelleKlamottenboerse->id}} @else text-danger @endif">
                                            {{$vknummer}}
                                            @if ($Nummer->first()->aktuelleKlamottenboerse->vergeben_an != "")
                                                - ist vergeben
                                            @elseif ($Nummer->first()->aktuelleKlamottenboerse->reserviert_fuer != "")
                                                - ist reserviert
                                            @else
                                                - vergeben und informieren
                                            @endif
                                        </a>

                                    @endforeach
                                @endif
                                <h6 role="separator" class="dropdown-divider"></h6>
                                <h6 class="dropdown-header">neue Nummer</h6>
                                <a class="dropdown-item" href="{{url('vknummer/'.$interessent->id.'/Nummervergeben')}}">neue Nummer vergeben</a>

                            </ul>
                        </div>
                    @endif



                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Übersicht Verkäufernummern
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @if ($interessent->bisherige_vknummen->count() > 0)
                            @foreach($interessent->bisherige_vknummen as $vknummer)
                                <li class="list-group-item ">
                                    <div class="row">
                                        <div class="col text-sm-left">
                                            {{$vknummer->klamottenboerse->datum->format('d.m.Y')}}
                                        </div>
                                        <div class="col">
                                            <b class="">
                                                {{$vknummer->vknummer}}
                                            </b>
                                        </div>
                                        <div class="col">
                                            <span class="pull-right label label-pill label-secondary"><small>{{number_format($vknummer->umsatz, 2)}} €</small></span>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        @else
                            Bisher keine Verkäufernummern
                        @endif
                    </ul>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Nachrichten
                    <span class="pull-right">
                            <div class="btn-group btn-group-sm">
                                <a class="btn btn-sm" href="{{url('/mail/'.$interessent->id)}}">
                                    <i class="font-icon font-icon-mail"></i>
                                </a>
                                <button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach($Vorlagen AS $Vorlage)
                                        <a class="dropdown-item" href="{{url('mail/'.$interessent->id.'/'.$Vorlage->id)}}">{{$Vorlage->name}}</a>
                                    @endforeach
                                </ul>
                            </div>
                        </span>
                </div>
                <div class="card-header pb-0 mb-0">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link @if ($mail_thread==false) active @endif" aria-current="page" href="{{url('interessent/'.$interessent->id.'/')}}">Eingang</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if ($mail_thread==true) active @endif" href="{{url('interessent/'.$interessent->id.'/Sent')}}">Ausgang</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body" id="nachrichten">
                    @foreach($messages as $message)
                        <div class="mail-box-item mail-box-item-clickable" @if (!$message->getFlags()->get('seen'))) style="background-color: #facd97;" @endif" data-id="{{$message->getUid()}}" >
                        <div class="mail-box-item-header">
                            <div class="mail-box-item-photo align-content-center">
                                @if ($message->getFrom()[0]->mail == $interessent->mail)
                                    <div class="btn btn-sm btn-rounded ">
                                        Von
                                    </div>
                                @else
                                    <div class="btn btn-sm btn-rounded ">
                                        An
                                    </div>
                                @endif
                            </div>
                            <div class="tbl mail-box-item-head-tbl mail-box-item-clickable">
                                <div class="tbl-row">
                                    <div class="tbl-cell">
                                        <div class="tbl mail-box-item-user-tbl">
                                            <div class="tbl-row">
                                                <div class="tbl-cell tbl-cell-name">
                                                    {{$message->getFrom()[0]}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tbl-cell tbl-cell-date">
                                        {{\Carbon\Carbon::parse($message->getDate())->format('d.m.Y H:i')}}
                                    </div>
                                </div>
                            </div>
                            <div class="mail-box-item-title mail-box-item-clickable">
                                {{$message->getSubject()}}
                            </div>
                        </div>
                        <div class="mail-box-item-content mail-box-item-clickable">
                            <div class="attach">
                                @if ($message->getAttachments()->count() > 0)
                                    <i class="fa fa-paperclip"></i>
                                @endif
                            </div>
                            <div class="txt">
                                @if ($message->getTextBody() != "")
                                    {{\Illuminate\Support\Str::limit($message->getTextBody(), 450)}}
                                @else
                                    {{\Illuminate\Support\Str::limit($message->getHTMLBody(), 450)}}
                                @endif
                            </div>
                        </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    Notizen
                    @if (isset($interessent->notiz->notiz))
                        <span class="pull-right small">
                                    (aktualisiert: {{$interessent->notiz->updated_at->format('d.m.Y, H:i')}} Uhr)
                                </span>
                    @endif
                </div>
                <div class="card-body">
                    <form action="{{url('notiz')."/".$interessent->id}}" method="post" class="form-horizontal">
                        @csrf
                        @method('put')
                        <textarea name="notiz" class="form-control"
                                  rows="5">{{ isset($interessent->notiz->notiz) ? $interessent->notiz->notiz : ''  }}</textarea>

                        <button type="submit" class="btn btn-success btn-block">speichern</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


@if ($interessent->vknummern_vergeben)
    <div id="removeVergabeModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Wirklich die Verkäufernummer zurückziehen?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Soll die Vergabe der Verkäufernummer aufgehoben und der Verkäufer darüber informiert werden?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{url('vknummer/'.$interessent->vknummern_vergeben->id.'/remove')}}" method="post">
                        {{csrf_field()}}{{method_field('put')}}
                        <button type="submit" class="btn btn-danger">Ja, aufheben.</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@else
    <div id="VergabeModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nummer vergeben?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Soll die Verkäufernummer <b> <span id="modal_vknummer"></span></b> an {{$interessent->vorname}} {{$interessent->nachname}} vergeben werden?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{url('/vknummern/vergeben')}}" method="post">
                        {{csrf_field()}}{{method_field('put')}}
                        <input type="hidden" name="NummernID" id="nummernID" value="">
                        <input type="hidden" name="InteressentID" value="{{$interessent->id}}">
                        <button type="submit" class="btn btn-success">Ja, vergeben.</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal für Mail -->
    @include('mails.elements.mailModal')
@endif

    <div id="deleteInteressentModal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Diesen Interessenten wirklich löschen?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Soll der Interessent gelöscht und darüber informiert werden?</p>
                </div>
                <div class="modal-footer">
                    <form action="{{url('interessenten/'.$interessent->id)}}" method="post">
                        {{csrf_field()}}{{method_field('delete')}}
                        <button type="submit" class="btn btn-danger">Ja, löschen und informieren.</button>
                    </form>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @include('mails.elements.mailModal')

@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('css/pages/mail.css')}}">
    <link rel="stylesheet" href="{{asset('css/seperate/ribbons.min.css')}}">
@endsection

@section('js')
    @include('mails.elements.mailajax')
<script>
    $('.VKNummer').on('click', function (e) {
        var id = $(this).data('id');
        var nummer = $(this).data('nummer');
        $('#modal_vknummer').text(nummer);
        $('#nummernID').val(id);
        $('#VergabeModal').modal('show');
    });

    $('#deleteInteressentButton').on('click', function (e) {
        $('#deleteInteressentModal').modal('show');
        console.log(this)
    });

    $('#saveBtnForm').on('click', function (e) {

        var form = $('#InteressentenForm');
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // serializes the form's elements.
            beforeSend: function () {
            } ,
            success: function(data)
            {

                $.notify({
                    message: 'Daten wurden gespeichert.'
                    },{
                        // settings
                        type: 'success'
                    }
                );
                $('#headerName').text(data.vorname + ' ' + data.nachname);
                $('#vornameText').text(data.vorname);
                $('#nachnameText').text(data.nachname);
                $('#mailText').text(data.mail);

                $('#telefonText').text(data.telefon);
                $('#handyText').text(data.handy);
                $('#mitarbeiterText').text(data.mitarbeiter);
                $('#kinderhausText').text(data.kinderhaus);
                $('#anredeText').text(data.anrede);

                $('#form').toggle();
                $('#footer').toggle();
                $('#daten').toggle();

            },
            error: function (data) {
                var errors = data.responseJSON;

                $.notify({
                    message: 'Daten konnten nicht geändert werden.'},
                    {
                        // settings
                        type: 'danger'
                    }
                );

                $('#InteressentenForm').submit();
            }
        });

        e.preventDefault();
    });

        @if (!$errors->any())
        $(document).ready(function() {
            $('.hide').hide();
        });
        @else
        $('#daten').hide();
        @endif

        $('#editBtn').click(function() {
            $('#form').toggle();
            $('#footer').toggle();
            $('#daten').toggle();
        });


    </script>


@endsection
