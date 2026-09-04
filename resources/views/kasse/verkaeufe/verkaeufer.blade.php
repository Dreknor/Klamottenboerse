@extends("layouts.app")

@section("content")

    <div class="container-fluid">
        <div class="card ">
            <div class="card-header">
                <h3>Bisherige Verkäufe</h3>
            </div>
            <div class="card-header">
                <div class="row">
                    <div class="col">
                        <a class="btn btn-primary btn-block" href="{{url("kasse/verlauf")}}">Nach Verkauf sortieren</a>
                    </div>
                    <div class="col">
                        <a class="btn btn-primary btn-block" href="{{url("kasse/verlauf/verkaeufer")}}">Nach Verkäufer sortieren</a>
                    </div>
                    <div class="col">
                        <a class="btn btn-danger btn-block" href="{{route('verlauf.activate.edit')}}">Verkauf nachträglich bearbeiten</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="myTable">
                        <tr>
                            <th>Verkäufer</th>
                            <th >
                                Artikel / Betrag
                            </th>
                            <th>Gesamtbetrag</th>
                        </tr>
                        @if(isset($Verlauf) and count($Verlauf)>0)
                            @foreach($Verlauf AS $Verkaeufer)
                                @if(count($Verkaeufer->verkaufteArtikel) > 0)
                                    @php($Summe = 0)
                                    <tr>
                                        <th>
                                            {{ $Verkaeufer->vknummer }} <br>
                                            {{ $Verkaeufer->vergeben_an_Interessent->vorname }} {{ $Verkaeufer->vergeben_an_Interessent->nachname }}
                                        </th>
                                        <td>
                                            <ul class="list-group">
                                                @foreach($Verkaeufer->verkaufteArtikel AS $Artikel)
                                                    <li class="list-group-item">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                {{ $Artikel->artikelnummer }}
                                                            </div>
                                                            <div class="col-md-4">
                                                                {{ sprintf('€ %s', number_format($Artikel->betrag, 2)) }}
                                                            </div>
                                                        </div>
                                                    </li>
                                                    @php( $Summe+= $Artikel->betrag)
                                                @endforeach
                                            </ul>
                                         </td>
                                        <th>
                                            {{ sprintf('€ %s', number_format($Summe, 2)) }}
                                        </th>
                                    </tr>
                                @endif
                            @endforeach
                            <tr>
                                <th colspan="3">
                                    {{ $Verlauf->links() }}
                                </th>
                            </tr>
                        </table>
                    @else
                        Bisher keine Verkäufe erfolgt
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
