@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>Einstellungen</h3>
                </div>
                <div class="card-body">
                    <form class="form-horizontal" action="{{ url("kasse/settings") }}" method="post" id="settings">
                        @csrf
                        <div class="form-group">
                                <label class="">Name des Flohmarktes</label>
                                <div class="">
                                    <input name="name" class="form-control" placeholder="Name des Flohmarktes" @if(isset($Settings->name)) value="{{ $Settings->name }}" @endif>
                                </div>
                        </div>
                            <div class="form-group">
                                <label class="">Name der Einrichtung</label>

                                <div class="">
                                    <input name="kinderhaus" class="form-control" placeholder="Name der Einrichtung" @if(isset($Settings->kinderhaus)) value="{{ $Settings->kinderhaus }}" @endif>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-6">Datum</label>

                                <div >
                                    <input name="datum" type="date" class="form-control" placeholder="Verkaufstag" @if(isset($Settings->datum)) value="{{ $Settings->datum->format('Y-m-d') }}" @endif>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="">Verkaufsprovision in %</label>

                                <div class="">
                                    <div class="input-group">
                                        <input name="provision" type="number" step="1" class="form-control" placeholder="Provision" @if(isset($Settings->provision)) value="{{ $Settings->provision }}" @endif>
                                    </div>
                                </div>
                            </div>
                    </form>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success" form="settings" >speichern </button>
                </div>
            </div>
        </div>

        <div class="col">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3>
                        Auswertung
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @if(isset($Kunden))
                            <li class="list-group-item">Kunden bisher:
                                <span class="badge">{{ $Kunden }}</span>
                            </li>
                        @endif
                        @if(isset($Teile))
                             <li class="list-group-item">verkaufte Artikel:
                                 <span class="badge">{{ $Teile }}</span>
                             </li>
                        @endif
                        @if(isset($Umsatz))
                             <li class="list-group-item">Umsatz:
                                 <span class="badge">{{ sprintf('€ %s', number_format($Umsatz, 2)) }}</span>
                             </li>
                        @endif
                            @if(isset($Kinderhaus))
                                <li class="list-group-item">Erlös:
                                    <span class="badge">{{ sprintf('€ %s', number_format($Kinderhaus, 2)) }}</span>
                                </li>
                            @endif
                            @if(isset($erfolgreichsteVKnummer))
                                <li class="list-group-item">bester Verkäufer:
                                    <span class="badge">{{ $erfolgreichsteVKnummer->vergeben_an_Interessent->vorname }} {{ $erfolgreichsteVKnummer->vergeben_an_Interessent->nachname }}</span>
                                </li>
                                <li class="list-group-item">
                                    Umsatz best. VK.
                                    <span class="badge">{{ sprintf('€ %s', number_format($erfolgreichsteVKnummer->sum, 2)) }}</span>
                                </li>
                            @endif
                    </ul>
                </div>
                <div class="card-footer">
                    @if(isset($warenkorb) and $warenkorb == 0)
                        <a href="{{url("kasse/Auswertung")}}" class="btn btn-warning">Abrechnung erstellen</a>
                        <a href="{{url("kasse/export")}}" class="btn btn-info">Export</a>
                    @else
                        <a href="{{url("kasse/Auswertung")}}" class="btn btn-warning disabled" >Abrechnung nicht möglich</a>
                        <p><small>Warenkörbe nicht leer.</small></p>
                    @endif

                    <!--  <a href="{{url("kasse/import")}}" class="btn btn-info">Nummern importieren</a> -->
                </div>
            </div>
        </div>
    </div>

@endsection
