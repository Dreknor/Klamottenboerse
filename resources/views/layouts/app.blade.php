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

    <link href="{{asset('css/bootstrap-datetimepicker.css')}}" rel="stylesheet" type="text/css">
    <script src="{{asset('js/bootstrap-datetimepicker.js')}}"></script>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />

    <script type="text/javascript" src="{{asset('js/DataTables-1.10.12/js/jquery.dataTables.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/DataTables-1.10.12/js/dataTables.bootstrap.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/AutoFill-2.1.2/js/dataTables.autoFill.js')}}"></script>
    <script type="text/javascript" src="{{asset('js/AutoFill-2.1.2/js/autoFill.bootstrap.js')}}"></script>

    


</head>

<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            @include('elements.navigation')
        </div>
    </nav>
    
    @if(session('Meldung'))
        <div class="container">
            <div class="row">
                <div class="col-md-10" >
                    <div class="alert alert-{{session('type')}} alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        {{session('Meldung')}}

                    </div>
                </div>
            </div>
        </div>
    @endif

    @yield('content')

    <!-- JavaScripts -->

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
    {{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
