@extends("layouts.app")

@section("content")
<div class="container-fluid">
    <div class="card">
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
                        <table class="table table-bordered table-striped" id="myTable">
                            <tr>
                                <th ></th>
                                <th>
                                    Artikel / Betrag / Betrag
                                </th>
                                <th>
                                    Summe
                                </th>

                                <th>
                                    @if(isset($edit) and $edit == true)
                                        bearbeiten
                                    @endif
                                </th>


                            </tr>
                            @if(isset($Verlauf) and count($Verlauf)>0)
                                @foreach($Verlauf AS $Verkauf)
                                    <tr>
                                        <th>
                                            {{ $Verkauf->created_at->format('d.m.Y') }}
                                            <br>
                                            {{ $Verkauf->created_at->format('H:i') }} Uhr
                                        </th>
                                        <td >
                                            @if(count($Verkauf->artikel)>0)
                                                <ul class="list-group">
                                                    @foreach($Verkauf->artikel AS $Artikel)
                                                        <li class="list-group-item">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    {{ $Artikel->vknummer }}
                                                                </div>
                                                                <div class="col-md-4">
                                                                    {{ $Artikel->artikelnummer }}
                                                                </div>
                                                                <div class="col-md-4">
                                                                    {{ sprintf('€ %s', number_format($Artikel->betrag, 2)) }}
                                                                </div>
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                        <th>
                                            {{ sprintf('€ %s', number_format($Verkauf->summe, 2)) }}
                                        </th>
                                        @if(isset($edit) and $edit == true)
                                            @if($Verkauf->user_id == \Illuminate\Support\Facades\Auth::user()->id or \Illuminate\Support\Facades\Auth::user()->is_admin == true)
                                                <td>
                                                    <a href="{{url("kasse/verlauf/$Verkauf->id/edit")}}">
                                                        <span class="glyphicon glyphicon-pencil"></span>
                                                    </a>
                                                </td>
                                            @else
                                                <td>Fehlende Berechtigung</td>
                                            @endif
                                        @else
                                            <td></td>
                                        @endif
                                    </tr>
                                @endforeach
                            @else
                                Bisher keine Verkäufe erfolgt
                            @endif
                        </table>
                        {{ $Verlauf->links() }}
                    </div>
                </div>
        </div>
</div>
@endsection
