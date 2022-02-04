@extends('layouts.app')

@section('content')

    <header class="page-content-header widgets-header">
        <div class="container-fluid">
            <div class="tbl tbl-outer">
                <div class="tbl-row">
                    <div class="tbl-cell">
                        <div class="tbl tbl-item">
                            <div class="tbl-row">
                                <div class="tbl-cell">
                                    <div class="title">aktuelle Klamottenbörse</div>
                                    <div class="amount color-blue">{{$Klamottenboersen->last()->datum->format('d.m.Y')}}</div>
                                    <div class="amount-sm">
                                        @if ($Klamottenboersen->last()->datum > now())
                                            in {{$Klamottenboersen->last()->datum->diffInDays(now())}} Tagen
                                        @else
                                            vor {{$Klamottenboersen->last()->datum->diffInDays(now())}} Tagen
                                        @endif
                                    </div>
                                </div>
                                <div class="tbl-cell">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tbl-cell">
                        <div class="tbl tbl-item">
                            <div class="tbl-row">
                                <div class="tbl-cell">
                                    <div class="title">Interessenten</div>
                                    <div class="amount color-blue">
                                        {{$Klamottenboersen->last()->vknummern_vergeben->count()}} Verkäufer
                                    </div>
                                    <div class="amount-sm">
                                        {{$Interessenten->count()}} Interessenten
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tbl-cell">
                        <div class="tbl tbl-item">
                            <div class="tbl-row">
                                <div class="tbl-cell">
                                    <div class="title">E-Mails</div>
                                    <div class="amount " id="unreadMails">

                                    </div>
                                    <div class="amount-sm" id="countMails">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tbl tbl-outer">
                <div class="tbl-row">
                    <div class="progress" style="height: 2px;">
                        <div class="progress-bar" id="bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>
    </header>



    <div class="container-fluid">
        <div class="row">
            <div class=" col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                            Entwicklung
                    </div>
                    <div class="card-body bg-light">
                        <canvas id="lineChart"></canvas>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class=" table-responsive">
                                    <table id="InterressentenTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>Datum</th>
                                            <th>Umsatz</th>
                                            <th>Verkäufer</th>
                                            <th>Teile</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($Klamottenboersen->sortByDesc('datum')->all() AS $klamottenboerse)
                                            <tr>
                                                <td>
                                                    {{ $klamottenboerse->datum->format('d.m.Y') }}
                                                </td>
                                                <td >
                                                    <span class="pull-right">
                                                        {{ number_format($klamottenboerse->vknummern->sum('umsatz'),2) }} €
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="pull-right">
                                                        {{ $klamottenboerse->vknummern->where('vergeben_an', '>', 0)->count() }}
                                                    </span>

                                                </td>
                                                <td>
                                                    <span class="pull-right">
                                                       {{ $klamottenboerse->maxTeile }}
                                                    </span>
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
            </div>
            <div class=" col-md-6 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        Posteingang
                    </div>
                    <div class="card-body">
                        <img src="{{asset('img/ajax-loader.gif')}}" id="wait" width="100px">
                        <ul class="list-group" id="nachrichten">

                            <!-- fetch Mails befor show

                            -->
                        </ul>



                    </ul>
                </div>
            </div>

        </div>
        </div>
    </div>

    <!-- Modal für Mail -->
    @include('mails.elements.mailModal')
@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('css/widgets.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/mail.css')}}">

@stop
@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
    <script>
        $('document').ready(function () {
            var back = ["#ff0000", '#FB8612', '#1EFB76', '#429dfb', '#fbf182'];
            var rand = back[Math.floor(Math.random() * back.length)];
            $('#bar').css('background',rand);

            $("#bar").animate({width: '100%'}, 300000, 'swing',function () {
                window.location.reload()
            });

        });
    </script>

    <script>
        //line
        var ctxL = document.getElementById("lineChart").getContext('2d');
        var myLineChart = new Chart(ctxL, {
            type: 'line',
            data: {
                labels: [
                    @foreach($Klamottenboersen as $Klamottenboerse)
                        "{{$Klamottenboerse->datum->format('m/Y')}}",
                    @endforeach
                ],
                datasets: [{
                    label: "Umsätze",
                    data: [
                        @foreach($Klamottenboersen as $Klamottenboerse)
                        " {{$Klamottenboerse->vknummern->sum('umsatz') }}",
                        @endforeach
                    ],
                    backgroundColor: [
                        'rgba(105, 0, 132, .2)',
                    ],
                    borderColor: [
                        'rgba(200, 99, 132, .7)',
                    ],
                    borderWidth: 2
                },
                    {
                        label: "Verkäufer",
                        data: [
                            @foreach($Klamottenboersen as $Klamottenboerse)
                                " {{$Klamottenboerse->vknummern->where('vergeben_an', '>', 0)->count() }}",
                            @endforeach
                        ],
                        backgroundColor: [
                            'rgba(200, 0, 132, .2)',
                        ],
                        borderColor: [
                            'rgba(200, 99, 132, .7)',
                        ],
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true
            }
        });
    </script>

    <script>
                var urlMails = "{{url('/getMails/')}}";
                $.ajax({
                    dataType: "json",
                    url: urlMails,
                    beforeSend: function() { $('#wait').show(); },
                    complete: function() { $('#wait').hide(); },
                    success: function (data) {
                        var unread = 0;
                        var items = jQuery.parseJSON(JSON.stringify(data));
                        $('#countMails').text(Object.keys(items.Nachricht).length + " Nachrichten in den letzten Tagen.");

                        $.each(items.Nachricht, function (item, value) {
                            if (value.bodies.text) {

                                var text = value.bodies.text.content.substr(0, 150);
                            } else if (value.bodies.html) {
                                var temporalDivElement = document.createElement("div");
                                // Set the HTML content with the providen
                                temporalDivElement.innerHTML = value.bodies.html.content;
                                // Retrieve the text property of the element (cross-browser support)
                                var text = temporalDivElement.textContent.substr(0, 150) || temporalDivElement.innerText || "";

                            } else {
                                var text = "Mailtext konnte nicht geladen werden."
                            }


                            var datum = new Date(value.date.date);
                            var name = (value.interessent) ? value.interessent.vorname + ' ' +value.interessent.nachname :  (value.from[0].personal);

                            if (value.interessent) {
                                var urlInteressent = "{{url('interessent/')}}" + '/'+ value.interessent.id;
                                var button = '<a href="'+urlInteressent+'" class="btn btn-sm btn-rounded"> <i class="font-icon font-icon-user text-white"></i> </a>';
                                var buttonSpam = '<button id="spam" class="btn btn-danger btn-sm btn-rounded"> <i class="fa fa-exclamation-triangle"></i>  </button>';

                            } else {
                                var button = "";
                                var buttonSpam = '<button id="spam" class="btn btn-danger btn-sm btn-rounded"> <i class="fa fa-exclamation-triangle"></i>  </button>';
                            }

                            if (value.flags['seen'] == 0) {
                                var selected = "selected";
                                unread += 1;
                                $('#unreadMails').text(unread + " ungelesene Mails");
                                $('#unreadMails').addClass('color-blue');

                            } else {
                                var selected = "";
                            }


                            $("#nachrichten").append(
                                '<div class="mail-box-item '+ selected+'" data-id="'+ value.uid +'" data-interessent="' + urlInteressent + '">\n' +
                                '                                    <div class="mail-box-item-header">\n' +
                                '                                        <div class="mail-box-item-photo align-content-center">' +
                                '                                        '+button+
                                '                                        </div>\n' +
                                '                                        <div class="tbl mail-box-item-head-tbl">\n' +
                                '                                            <div class="tbl-row">\n' +
                                '                                                <div class="tbl-cell">\n' +
                                '                                                    <div class="tbl mail-box-item-user-tbl">\n' +
                                '                                                        <div class="tbl-row">\n' +
                                '                                                            <div class="tbl-cell tbl-cell-name">\n' +
                                '                                                            '+ name +
                                '                                                            </div>\n' +
                                '                                                        </div>\n' +
                                '                                                    </div>\n' +
                                '                                                </div>\n' +
                                '                                                <div class="tbl-cell tbl-cell-date">'+datum.toLocaleDateString() + ', '+ datum.toLocaleTimeString() +'</div>\n' +
                                '                                            </div>\n' +
                                '                                        </div>\n' +
                                '                                        <div class="mail-box-item-title">'+ value.subject.replace(/[_]/g, ' ') +'</div>\n' +
                                '                                    </div>\n' +
                                '                                    <div class="mail-box-item-content">\n' +
                                '                                        <div class="attach">\n' +
                                '                                        </div>\n' +
                                '                                        <div class="txt">\n' + text +
                                '                                        </div>\n' +
                                '                                    </div>\n' +
                                '                                </div>');


                        });

                    },
                    error: function (data) {
                        $("#nachrichten").append('<li class="mail-box-item bg-danger text-white">Fehler beim Laden der E-Mails</li>');
                        console.log(data);
                    }
                });


    </script>


        @include('mails.elements.mailajax')

@stop