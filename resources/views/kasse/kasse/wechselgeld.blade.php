@extends("layouts.app")

@section('content')

    <div class="row">
        <div class="col-md-offset-2 col-md-8">
            <div class="row">
                <div class="col-lg-12">
                    <div class="jumbotron alert-success text-center">
                        <h1>Das Wechselgeld beträgt: {{sprintf(' %s', number_format($Wechselgeld, 2))   }} €</h1>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <p><a class="btn btn-primary btn-block" href="{{ url("/kasse") }}">
                            <span style="font-size: large">neuer Kunde </span>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>



@endsection
