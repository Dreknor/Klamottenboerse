<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>



    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <link rel="stylesheet" href="{{asset('css/login.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/font-awesome/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/bootstrap/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/main.css')}}">
    <link rel="stylesheet" href="{{asset('css/liste.css')}}">

    <style>
        body {
            background-color: #ff6900;
        }
    </style>
    @yield('css')
</head>
<body >
<div class="page-center">
    <div class="page-center-in">
        <div class="container-fluid">
            <div class="helfer-box">
                <div class="card">
                    <div class="card-header">
                        <h3>Helferliste</h3>
                        <p>
                            Vielen Dank, dass du uns bei der Klamottenbörse am {{$klamottenboerse->datum->format('d.m.Y')}} unterstützen möchtest.
                        </p>
                        <p>
                            Bitte trage dich hier ein, wenn du uns helfen möchtest. Wir benötigen E-Mail und Telefon, damit wir dich im Notfall erreichen können.
                        </p>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    <p class="card-body">
                    <div class="container-fluid">
                        @if(count($termine) > 0)
                            <form action="{{ route('helfer.store') }}" method="post" class="form-horizontal">
                                @csrf
                                <div class="form-group">
                                    <label for="termin">Helfer werden benötigt:</label>
                                    <select name="termin" class="form-control">
                                        @foreach($termine as $termin)
                                            <option value="{{$termin->id}}">{{ \App\Model\Appointment::BEREICHE[$termin->bereich] ?? $termin->bereich }}: {{$termin->date_start->format('d.m.Y H:i')}} - {{$termin->date_end->format('H:i')}} Uhr: {{$termin->beschreibung}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" class="form-control" value="{{old('name')}}">
                                </div>
                                <div class="form-group">
                                    <label for="email">E-Mail</label>
                                    <input type="email" name="mail" class="form-control" value="{{old('mail')}}">
                                </div>
                                <div class="form-group">
                                    <label for="telefon">Telefon</label>
                                    <input type="text" name="telefon" class="form-control" value="{{old('telefon')}}">
                                </div>
                                <button type="submit" class="btn btn-block btn-primary">als Helfer eintragen</button>
                            </form>
                        @else
                            <p>Aktuell sind keine Helferaufgaben zu vergeben</p>
                        @endif
                    </div>


                </div>
            </div>
        </div>
    </div>
</div><!--.page-center-->

<script src="js/lib/jquery/jquery-3.2.1.min.js"></script>
<script src="js/lib/popper/popper.min.js"></script>
<script src="js/lib/tether/tether.min.js"></script>
<script src="js/lib/bootstrap/bootstrap.min.js"></script>
<script src="js/plugins.js"></script>
<script type="text/javascript" src="js/lib/match-height/jquery.matchHeight.min.js"></script>
<script>
    $(function() {
        $('.page-center').matchHeight({
            target: $('html')
        });

        $(window).resize(function(){
            setTimeout(function(){
                $('.page-center').matchHeight({ remove: true });
                $('.page-center').matchHeight({
                    target: $('html')
                });
            },100);
        });
    });
</script>
<script src="js/app.js"></script>
</body>
</html>
