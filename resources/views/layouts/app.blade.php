<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Laravel') }}</title>



	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->

    <link rel="stylesheet" href="{{asset('css/lib/font-awesome/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/lib/bootstrap/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/main.css')}}">

	@yield('css')
</head>
<body class="with-side-menu theme-picton-blue">

	<header class="site-header">
	    <div class="container-fluid" >
	        <a href="{{url('/')}}" class="navbar-brand col-sm-auto text-white">
				Klamottenbörse
	        </a>
	
	        <button id="show-hide-sidebar-toggle" class="show-hide-sidebar text-white">
	            <span class="">toggle menu</span>
	        </button>
	
	        <button class="hamburger hamburger--htla">
	            <span>toggle menu</span>
	        </button>
	        <div class="site-header-content">
	            <div class="site-header-content-in">
	                <div class="site-header-shown">

	                    <div class="dropdown dropdown-notification messages">
	                        <a href="#"
	                           class="header-alarm dropdown-toggle" id="NachrichtenToogle">
	                            <i class="font-icon-mail"></i>
	                        </a>

	                    </div>

	                    <div class="dropdown user-menu">
	                        <button class="dropdown-toggle" id="dd-user-menu" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                            <img src="{{asset('img/avatar-2-64.png')}}" alt="">
	                        </button>
	                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dd-user-menu">

	                            <div class="dropdown-divider"></div>
                                <form class="" action="{{url('/logout')}}" method="post">
                                    {{ csrf_field() }}
                                    <button type="submit" name="logout" class="dropdown-item"><span class="font-icon glyphicon glyphicon-log-out"></span> Logout</button>
                                </form>
	                        </div>
	                    </div>
                    </div>
	            </div><!--site-header-content-in-->
	        </div><!--.site-header-content-->
	    </div><!--.container-fluid-->
	</header><!--.site-header-->

	<div class="mobile-menu-left-overlay"></div>
	<nav class="side-menu">
	    <ul class="side-menu-list">
			<li class="">
				<a href="{{url('/home')}}">
	            <span>
	                <i class="font-icon font-icon-dashboard"></i>
	                <span class="lbl">Dashboard</span>
	            </span>
				</a>
			</li>



	        <li class="with-sub">
	            <span>
	                <i class="font-icon glyphicon glyphicon-user"></i>
	                <span class="lbl">Interessenten</span>
	            </span>
	            <ul>
	                <li><a href="{{url('interessenten')}}"><span class="lbl">Übersicht</span></a></li>
	                <li><a href="#"><span class="lbl">Anlegen</span></a></li>
	            </ul>
	        </li>
	        <li class="with-sub">
	            <span>
	                <i class="font-icon glyphicon glyphicon-calendar "></i>
	                <span class="lbl">Klamottenbörse</span>
	            </span>
	            <ul>
	                <li><a href="{{url('grunddaten')}}"><span class="lbl">Grunddaten</span></a></li>
	                <li><a href="{{url('vknummern')}}"><span class="lbl">Verkäufernummern</span></a></li>
                    <li><a href="#"><span class="lbl">Listen</span></a></li>

                </ul>
	        </li>
            <li class="with-sub">
	            <span>
	                <i class="font-icon glyphicon glyphicon-calendar "></i>
	                <span class="lbl">Settings</span>
	            </span>
                <ul>
                    <li><a href="#"><span class="lbl">Mail-Vorlagen</span></a></li>
                </ul>
            </li>

	    </ul>
	

	</nav><!--.side-menu-->

	<div class="page-content">
		<div class="container-fluid">
			@yield('content')
		</div><!--.container-fluid-->
	</div><!--.page-content-->

	<script src="{{asset('js/lib/jquery/jquery-3.2.1.min.js')}}"></script>
	<script src="{{asset('js/lib/popper/popper.min.js')}}"></script>
	<script src="{{asset('js/lib/tether/tether.min.js')}}"></script>
	<script src="{{asset('js/lib/bootstrap/bootstrap.min.js')}}"></script>
	<script src="{{asset('js/plugins.js')}}"></script>
	<script src="{{asset('js/app.js')}}"></script>



	@yield('js')

	<script src="{{asset('js/lib/bootstrap-notify/bootstrap-notify.min.js')}}"></script>
	<script src="{{asset('js/lib/bootstrap-notify/bootstrap-notify-init.js')}}"></script>
	@if (session('success') )

		<script>
            $.notify({
                message: '{{ session('success') }}'
				},{
                    // settings
                    type: 'success'
                }
            );
		</script>

	@endif

	@if (session('fehler') )

		<script>
            $.notify({
                message: '{{ session('fehler') }}'
				},{
                    // settings
                    type: 'danger'
                }
            );
		</script>

	@endif

	<script>
        var url = "{{url('/unreadMail')}}";
        $.ajax({
            dataType: "json",
            url: url,
            success: function (data) {
                if (data.Nachrichten > 0){
                    $('#NachrichtenToogle').addClass('active');
				} else {
                    $('#NachrichtenToogle').removeClass('active');
				}
            },
            error: function (data) {
                console.log("Fehler " + data );
            }
        });
	</script>
</body>
</html>