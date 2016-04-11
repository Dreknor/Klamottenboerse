<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Klamottenbörse</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap.css')}}"/>


    <script src="{{asset('js/jquery-2.2.2.min.js')}}"></script>
    <!-- x-editable -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>


    {{--<link href="css/bootstrap.css" rel="stylesheet">--}}
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}


</head>

<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="row">
                <div class="navbar-header">
                    <h1 class="navbar-brand" href="{{ url('/') }}">
                        Klamottenbörsen-Verwaltung
                    </h1>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div class="panel-body alert alert-danger">
                    Sie wurden erfolgreich abgemeldet.
                </div>
            </div>
        </div>
    </div>




        <!-- JavaScripts -->

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
