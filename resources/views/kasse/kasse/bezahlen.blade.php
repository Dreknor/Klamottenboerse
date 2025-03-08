@extends("layouts.app")

@section('content')

    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="jumbotron alert-success text-center">
                                <h1>Der Kunde muss <b>{{$Summe}} €</b> bezahlen.</h1>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a class="btn btn-primary btn-block" href="{{ url("/kasse") }}">
                                <span style="font-size: large">neuer Kunde </span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-xs-offset-1 col-auto">
            <div class="card ">
                <div class="card-header bg-warning">Wechselgeld</div>
                <div class="card-body">
                    <div class="table-responsive-md">
                        <table class="table table-bordered table-striped table-condensed">
                            <tr>
                                <th>gegeben</th>
                                <th>Wechselgeld</th>
                            </tr>
                            @for($x = ceil($Summe / 100) * 100; $x > $Summe; $x -= 10)
                                <tr >
                                    <td>{{ $x }} €</td>
                                    <td>{{ $x-$Summe }} €</td>
                                </tr>
                            @endfor
                        </table>
                    </div>


                </div>

                <div class="card-footer">
                    <form action="{{url('kasse/wechselgeld')}}" method="post" class="form-horizontal">
                        @csrf
                        <input type="hidden" name="betrag" value="{{ $Summe }}">
                        <div class="input-group">
                            <input name="gegeben" placeholder="Gegebener Betrag" class="form-control" type="number" step="0.01" min="{{ $Summe }}" autofocus>
                        </div>

                        <input type="submit" class="btn btn-sm btn-success form-control" value="Wechselgeld berechnen">
                    </form>
                </div>

            </div>
        </div>
    </div>



@endsection
