@extends('layouts.app')

@section('js')
    <script src="{{asset('js/lib/datatables-net/datatables.min.js')}}"></script>
    <script src="{{asset('js/lib/datatables-net/buttons-1.2.0/js/dataTables.buttons.js')}}"></script>
    <script src="{{asset('js/lib/datatables-net/buttons-1.2.0/js/buttons.bootstrap4.min.js')}}"></script>
    <script>
        $(function() {
            var table = $('#interessenten').DataTable({

                dom: '<"row"<"col-8"f><"col-auto"<"float-right"B>>>t<"bottom"lp><"clear">',
                "pageLength": 50,

                buttons: [
                    { extend: 'copy', className: 'btn ' },
                    { extend: 'excel', className: 'btn' },
                    { extend: 'pdf', className: 'btn' }
                ]
            });

            $('#BtnAlle').click(function() {
                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('Alle Interessenten');
                table
                    .search( '' )
                    .columns().search( '' )
                    .draw();
            });

            $('#btnMitarbeiter').click(function() {
                table.search( '' )
                    .columns().search( '' )
                    .column( 6 ).search( 'ja')
                    .draw();
                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('Mitarbeiter');
            });

            $('#btnKinderhaus').click(function() {
                table.search( '' )
                    .columns().search( '' )
                    .column( 5 ).search( 'ja')
                    .draw();

                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('Kinderhaus');
            });

            $('#btnVerkäufer').click(function() {
                table.search( '' )
                    .columns().search( '' )
                    .column( 7 ).search('^[0-9]', true)
                    .draw();
                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('Verkäufer');
            });

            $('#btnNoNumber').click(function() {
                table.search( '' )
                    .columns().search( '' )
                    .column( 7 ).search( '^$', true, false)
                    .draw();
                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('ohne Nummer');
            });

            $('#btnWarteliste').click(function() {
                table.search( '' )
                    .columns().search( '' )
                    .column( 7 ).search( 'Warteliste')
                    .draw();
                $( this ).closest('#btns').find( '.active' ).removeClass( 'active' );
                $( this ).addClass( 'active' );
                $('#subtitle').text('Warteliste');
            });
        });


    </script>
@stop

@section('css')
    <link rel="stylesheet" href="{{asset('css/lib/datatables-net/datatables.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/bootstrap/dataTables.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/datatables-net/buttons-1.2.0/css/buttons.bootstrap4.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/datatables-net/buttons-1.2.0/css/buttons.dataTables.min.css')}}">


@stop

@section('content')
    <div class="container-fluid">
        <header class="section-header">
            <div class="tbl">
                <div class="tbl-row">
                    <div class="tbl-cell">
                        <h2 id="Ueberschrift">Interessenten</h2>
                        <div class="subtitle" id="subtitle">Alle Interessenten</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="card">
                <div class="card-body">
                        <div class="row" id="btns">
                            <div class=" col">
                                <a class="btn btn-block active btn-primary-outline" id="BtnAlle">
                                    Alle
                                    <span class="label label-pill label-info">{{$interessenten->count()}}</span>
                                </a>
                            </div>
                            <div class=" col">
                                <a class="btn btn-block btn-primary-outline" id="btnMitarbeiter">
                                    Mitarbeiter
                                    <span class="label label-pill label-info">{{$interessenten->where('mitarbeiter', 'ja')->count()}}</span>
                                </a>
                            </div>
                            <div class=" col">
                                <a class="btn btn-block btn-primary-outline" id="btnKinderhaus">
                                    Kinderhaus
                                    <span class="label label-pill label-info ">{{$interessenten->where('kinderhaus', 'ja')->count()}}</span>
                                </a>
                            </div>
                            <div class=" col">
                                <a class="btn btn-block btn-primary-outline" id="btnVerkäufer">
                                    Verkäufer
                                    <span class="label label-pill label-info">{{$interessenten->where('vknummern_vergeben', '!=','')->count()}}</span>
                                </a>
                            </div>
                            <div class=" col">
                                <a class="btn btn-block btn-primary-outline"  id="btnNoNumber">
                                    ohne Nummer
                                    <span class="label label-pill label-info">{{$interessenten->where('vknummern_vergeben', '=','')->count()}}</span>
                                </a>
                            </div>
                            <div class=" col">
                                <a class="btn btn-block btn-primary-outline" id="btnWarteliste">
                                    Warteliste
                                    <span class="label label-pill label-info">{{$interessenten->where('warteliste','!=','')->count()}}</span>
                                </a>
                            </div>
                        </div>
                </div>
        </section>

        </section>
        <section class="card">
            <div class="card-block">
                <div class="table-responsive">
                    <table class="table table-s table-bordered table-striped" id="interessenten">
                        <thead>
                            <tr>
                                <th style="max-width: 10%;">

                                </th>
                                <th>
                                    Nachname
                                </th>
                                <th>
                                    Vorname
                                </th>
                                <th>
                                    E-Mail
                                </th>
                                <th>
                                    Telefon
                                </th>
                                <th>
                                    Kinderhaus
                                </th>
                                <th>
                                    Mitarbeiter
                                </th>
                                <th>
                                    VK-Nummer
                                </th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($interessenten as $interessent)
                                <tr style="height: 5px;">
                                    <td style="max-width: 10%;">
                                        <a href="{{url('interessent').'/'.$interessent->id}}" class="link">
                                            <span class="glyphicon glyphicon-eye-open"></span>
                                        </a>
                                    </td>
                                    <td>
                                        {{$interessent->nachname}}
                                    </td>
                                    <td>
                                        {{$interessent->vorname}}
                                    </td>
                                    <td>
                                        {{$interessent->mail}}
                                    </td>
                                    <td>
                                        {{$interessent->telefon}}
                                        @if ($interessent->telefon != "" and $interessent->handy !="")
                                            <br>
                                        @endif
                                        {{$interessent->handy}}

                                    </td>
                                    <td>
                                        {{$interessent->kinderhaus}}
                                    </td>
                                    <td>
                                        {{$interessent->mitarbeiter}}
                                    </td>
                                    <td>
                                        @if ($interessent->vknummern_vergeben != NULL)
                                            {{$interessent->vknummern_vergeben->vknummer}}
                                        @endif

                                        @if ($interessent->warteliste)
                                            Warteliste ({{$interessent->warteliste->created_at->format('d.m.Y H:i')}})
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>

                        </tfoot>

                    </table>
                </div>

            </div>
        </section>
    </div>
@stop