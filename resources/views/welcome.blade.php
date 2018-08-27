@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-6">
            <div class="row">
                <div class="panel panel-warning">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-lg-6">
                                <h4>
                                    aktuelle Klamottenbörse am {{$Klamottenboerse->datum->format("d.m.Y")}}
                                </h4>
                            </div>
                            <div class="col-lg-6">
                                @if ($Klamottenboerse->datum->copy()->addMonths(3) < \Carbon\Carbon::now())
                                    <span class="pull-right">
                                                    <a class="btn btn-danger" href="{{url('Grunddaten/abschliessen')}}">Klamottenbörse abschließen</a>
                                                </span>
                                @endif
                            </div>
                        </div>


                    </div>

                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                Anzahl Interessenten:
                            </div>
                            <div class="col-md-6">
                            <span class="badge">
                                {{ $Interessenten }}
                            </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                aktuelle Verkäufer:
                            </div>
                            <div class="col-md-6">
                            <span class="badge">
                                {{ $Verkaeufer }}
                            </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>
                            vorhergehende Klamottenbörsen
                        </h4>
                    </div>
                    <div class="panel-body">
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
                                        @foreach($Klamottenboersen AS $klamottenboerse)
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


        </div>
        <div class="col-lg-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                            <h4>
                                letzte Nachrichten
                            </h4>
                </div>
                <div class="panel-body">
                    <div class="list-group">
                        @foreach ($Nachrichten as $nachricht)
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
                                {{ $nachricht->created_at->format('d.m.Y H:i') }} - {{$nachricht->Interessent->vorname}} {{$nachricht->Interessent->nachname}} <br>
                                {{ $nachricht->betreff }}
                            </button>
                        @endforeach
                    </div>
                </div>

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


<script type="text/javascript">

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
</script>
@endsection
