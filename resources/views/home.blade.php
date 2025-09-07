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
                                        {{$Klamottenboersen->last()->vknummern_vergeben_count}} Verkäufer
                                    </div>
                                    <div class="amount-sm">
                                        {{$Interessenten}} Interessenten
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
                    <div class="tbl-cell">
                        <div class="tbl tbl-item">
                            <div class="tbl-row">
                                <div class="tbl-cell">
                                    <div class="btn btn-sm hidden" id="showChart">
                                        <i class="fa fa-eye"></i> Entwicklung anzeigen
                                    </div>
                                </div>
                                <div class="tbl-cell">
                                    <div class="btn btn-sm hidden" id="showMail">
                                        <i class="fa fa-eye"></i> E-Mails anzeigen
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
            <div class=" col" id="chartCol">
                <div class="card">
                    <div class="card-header">
                            Entwicklung
                        <div class="pull-right">
                            <div class="btn btn-sm" id="chartBtn">
                                <i class="fa fa-eye-slash"></i>
                            </div>

                        </div>
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
                                                        {{ number_format($klamottenboerse->vknummern()->withoutGlobalScopes()->get()->sum('umsatz'), 2) }} €
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="pull-right">
                                                        {{ $klamottenboerse->vknummern_vergeben_count }}
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
            <div class=" col" id="mailCol">
                <div class="card">
                    <div class="card-header">
                        Posteingang
                        <div class="pull-right">
                            <div class="btn btn-sm" id="mailBtn">
                                <i class="fa fa-eye-slash"></i>
                            </div>

                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="list-group" id="nachrichten">
                            @foreach($messages as $message)
                                <div class="mail-box-item" @if (!$message->getFlags()->get('seen'))) style="background-color: #facd97;" @endif" data-id="{{$message->getUid()}}" >
                                    <div class="mail-box-item-header">
                                        <div class="mail-box-item-photo align-content-center">

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
                            @if($Klamottenboerse->vknummern()->withoutGlobalScopes()->get()->sum('umsatz') > 0)
                                "{{$Klamottenboerse->datum->format('m/Y')}}",
                            @endif
                        @endforeach
                ],
                datasets: [
                    {
                        label: "Umsätze",
                        data: [
                            @foreach($Klamottenboersen as $Klamottenboerse)
                                 @if($Klamottenboerse->vknummern()->withoutGlobalScopes()->get()->sum('umsatz') > 0)
                                    " {{$Klamottenboerse->vknummern()->withoutGlobalScopes()->get()->sum('umsatz') }}",
                                 @endif
                            @endforeach
                        ],
                        yAxisID: 'y',
                    },
                    {
                        label: "Verkaufsvorgänge",
                        data: [
                            @foreach($Klamottenboersen as $Klamottenboerse)
                                @if($Klamottenboerse->vknummern_sum_umsatz > 0)
                                    "{{$Klamottenboerse->verkaeufe_count}}",
                                @endif
                            @endforeach
                        ],
                        yAxisID: 'y1',

                    },
                    {
                        label: "verk Artikel",
                        data: [
                            @foreach($Klamottenboersen as $Klamottenboerse)
                                @if($Klamottenboerse->vknummern_sum_umsatz > 0)
                                    "{{$Klamottenboerse->verkaufte_artikel_count}}",
                                @endif
                            @endforeach
                        ],
                        yAxisID: 'y1',
                    },

                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        min: 0,


                        // grid line settings
                        grid: {
                            drawOnChartArea: false, // only want the grid lines for one axis to show up
                        },
                    }
                }
            }

        });
    </script>
    <script>
        $('#chartBtn').on('click', function () {
            $('#chartCol').hide();
            $('#showChart').removeClass('hidden');
        });
        $('#showChart').on('click', function () {
            $('#showChart').addClass('hidden');
            $('#chartCol').show();
        });

        $('#mailBtn').on('click', function () {
            $('#mailCol').hide();
            $('#showMail').removeClass('hidden');
        });
        $('#showMail').on('click', function () {
            $('#showMail').addClass('hidden');
            $('#mailCol').show();
        });


    </script>

        @include('mails.elements.mailajax')

@stop
