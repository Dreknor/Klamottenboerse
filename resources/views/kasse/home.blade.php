@extends('layouts.app')

@section('content')

<div class="container-fluid">


    <div class="row">
            <div class="col-md-8">

                <form class="form-horizontal" method="post" name="kasse" action="{{url("kasse/artikelBuchen")}}">
                @csrf
                @if (isset($Fehler))
                    <div class="alert alert-danger jumbotron">
                        <p>Verkäufernummer ist nicht vergeben!</p>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header @if ($errors->any()) bg-danger @else bg-primary @endif text-white ">
                        <h4>
                            Kasse
                        </h4>
                    </div>

                    <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    @if(isset($artikel->vknummer))
                                        <input name="vknummer" class="form-control" type='number' step='1' min="201" max="600" placeholder='Verkäufer-Nr.' value="{{$artikel->vknummer}}" autofocus required >
                                    @else
                                        <input name="vknummer" class="form-control" type='number' step='1' min="201" max="600" placeholder='Verkäufer-Nr.' value='{{old('vknummer', '')}}' autofocus required >
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    @if(isset($artikel->artikelnummer))
                                        <input name='artikelnummer' class="form-control" type='number' step='1' placeholder='Artikelnummer' value="{{$artikel->artikelnummer}}" required>
                                    @else
                                        <input name='artikelnummer' class="form-control" type='number' step='1' placeholder='Artikelnummer' value="{{old('artikelnummer', '')}}" required>
                                    @endif
                                </div>
                                <div class="col-md-4">
                                    @if(isset($artikel->betrag))
                                        <input name='betrag' class="form-control" type='number' step='0.50' placeholder='Betrag' value="{{$artikel->betrag}}" required>
                                    @else
                                        <input name='betrag' class="form-control" type='number' step='0.50' placeholder='Betrag' value="{{old('artikelnummer', 'betrag')}}" required>
                                    @endif
                                </div>
                            </div>

                    </div>
                    <div class="card-footer">
                        <input type="submit" name="submit"  class="btn btn-success" value="buchen">
                    </div>


                </form>
            </div>
        </div>
        <div class="col-md-3 ml-1">
            <div class="card bg-center">
                <div class="card-header bg-primary text-white">
                    <h4>Warenkorb</h4>
                </div>
                <div class="card-footer">
                    @if(isset($warenkorb) and $summe > 0)
                        <a href="{{url("/kasse/bezahlen")}}" class="btn btn-danger btn-block">Summe: {{ $summe }} €</a>
                    @endif
                </div>
                <div class="card-body">

                    <div class="row">
                    @if(isset($warenkorb) and count($warenkorb)>0)
                        <table class="table table-striped">
                            <tr>
                                <th>Verk.</th>
                                <th>Art.</th>
                                <th>Betrag</th>
                                <th> </th>
                            </tr>
                            @foreach($warenkorb AS $articel)
                                <tr>
                                    <td>{{ $articel->vknummer }}</td>
                                    <td>{{ $articel->artikelnummer }}</td>
                                    <td>{{ sprintf('€ %s', number_format($articel->betrag, 2)) }}</td>
                                    <td><a href="{{url("kasse/kasse/$articel->id/edit")}}"><span class="glyphicon glyphicon-pencil"></span> </a> </td>
                                </tr>

                            @endforeach

                        </table>

                    @else
                        <div class="col-md-12">
                        <p>Der Warenkorb ist leer</p>
                            </div>
                    @endif
                        </div>
                </div>
                <div class="card-footer">
                    {{ $warenkorb->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
