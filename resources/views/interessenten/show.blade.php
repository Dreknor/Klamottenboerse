@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <span  id="headerName">{{$interessent->vorname}} {{$interessent->nachname}}</span>
                        <span class="pull-right">
                            <a class="btn btn-warning btn-sm" id="editBtn">
                                 <i class="font-icon-pencil"></i>
                            </a>

                        </span>
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
                    </div>
                </div>
            </div>

            <div class="col">
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
                        @elseif (!($interessent->vknummern_vergeben) and $interessent->vknummer_reserviert)
                            <a class="btn btn-success btn-sm btn-block" href="{{url('vknummer/'.$interessent->vknummer_reserviert->id.'/vergeben')}}">Nummer <b>{{$interessent->vknummer_reserviert->vknummer}}</b> vergeben und informieren</a>
                            <a class="btn btn-warning btn-sm btn-block" href="{{url('vknummer/'.$interessent->vknummer_reserviert->id.'/reservierungAufheben')}}">Reservierung <b>{{$interessent->vknummer_reserviert->vknummer}}</b> aufheben</a>

                        @else
                            <a class="btn btn-success btn-sm btn-block">Nummer vergeben</a>
                            <a class="btn btn-warning-outline btn-sm btn-block" href="{{url('vknummer/'.$interessent->id.'/reservierung')}}">Nummer reservieren</a>
                        @endif



                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card">
                    <div class="card-header">
                        Übersicht Verkäufernummern
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @if ($interessent->bisherige_vknummen()->count() > 0)
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
            </div>

            <div class=" col-md-6 col-sm-12" >
                <div class="card">
                    <div class="card-header">
                        Nachrichten
                    </div>
                    <div class="card-body" >
                        <ul class="list" id="nachrichten">

                        </ul>
                        <img src="{{asset('img/ajax-loader.gif')}}" id="wait" width="100px">
                    </div>

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
@endif


@stop

@section('css')
    <link rel="stylesheet" href="{{asset('css/pages/mail.css')}}">
    <link rel="stylesheet" href="{{asset('css/seperate/ribbons.min.css')}}">
@stop

@section('js')
<script>


    $('#saveBtnForm').on('click', function (e) {

        var form = $('#InteressentenForm');
        var url = form.attr('action');

        $.ajax({
            type: "POST",
            url: url,
            data: form.serialize(), // serializes the form's elements.
            beforeSend: function () {
                console.log(form);
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
            }
        });

        e.preventDefault();
    });
</script>


    <script>
        var url = "{{url('/getUsermail/'.$interessent->id)}}";
        $.ajax({
            dataType: "json",
            url: url,
            beforeSend: function() { $('#wait').show(); },
            complete: function() { $('#wait').hide(); },
            success: function (data) {
                var items = jQuery.parseJSON(JSON.stringify(data.Nachrichten));

                    $.each(items, function (item, value) {
                        if ('text' in value.bodies) {
                            var text = value.bodies.text.content.substr(0, 400);
                        } else {
                            var temporalDivElement = document.createElement("div");
                            // Set the HTML content with the providen
                            temporalDivElement.innerHTML = value.bodies.html.content;
                            // Retrieve the text property of the element (cross-browser support)
                            var text = temporalDivElement.textContent.substr(0, 400) || temporalDivElement.innerText || "";

                        }

                        if (value.from[0].mail == "anmeldung@klamottenboerse.de") {
                            var color = "bg-info ";
                            var textcolor = "text-white";
                        } else {
                            var color = "";
                            var textcolor = "";
                        }

                        $("#nachrichten").append('<li class="mail-box-item ' + color + textcolor + '">\n' +
                            '                                <div class="mail-box-item-header">\n' +
                            '                                       <div class="mail-box-item-photo">\n' +
                            '                                           <img src="{{asset('img/avatar-1-48.png')}}" alt="">\n' +
                            '                                        </div>\n' +
                            '                                    <div class="tbl mail-box-item-head-tbl">\n' +
                            '                                        <div class="tbl-row">\n' +
                            '                                            <div class="tbl-cell">\n' +
                            '                                                <div class="tbl mail-box-item-user-tbl">\n' +
                            '                                                    <div class="tbl-row">\n' +
                            '                                                        <div class="tbl-cell tbl-cell-name ' + textcolor + '">' + value.from[0].full + '</div>\n' +
                            '                                                    </div>\n' +
                            '                                                </div>\n' +
                            '                                            </div>\n' +
                            '                                            <div class="tbl-cell tbl-cell-date ' + textcolor + '">' + value.date.date.substr(0, 19) + '</div>\n' +
                            '                                        </div>\n' +
                            '                                    </div>\n' +
                            '                                    <div class="mail-box-item-title ' + textcolor + '">' + value.subject + '</div>\n' +
                            '                                </div>\n' +
                            '                                <div class="mail-box-item-content">\n' +
                            '                                    <div class="txt ' + textcolor + '" style="word-wrap: break-word;"><small>' + text + '</small></div>\n' +
                            '                                </div>\n' +
                            '                            </div>');


                    });

            },
            error: function (data) {
                $("#nachrichten").append('<li class="mail-box-item bg-danger text-white">Fehler beim Laden der E-Mails</li>');
            }
        });
    </script>
    <script>
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
@stop