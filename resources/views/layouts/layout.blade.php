<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'RuneManager') }} | @yield('title')</title>

	<!-- Styles -->
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">
	<link href="{{ asset('css/style.css') }}" rel="stylesheet">

	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<body>
	<div id="app">
		<div class="container my-4">
			<div class="row flex-column-reverse flex-md-row">
				<div class="col-md-3">
					<nav class="navbar navbar-dark bg-dark background-panel-texture">
						<a class="navbar-brand runescape-large" href="#">@yield('title')</a>

						<button class="navbar-toggler dropdown-arrow-texture" type="button" data-toggle="collapse" data-target="#navbarDropdown"
						aria-controls="navbarDropdown" aria-expanded="false" aria-label="Toggle navigation" style="border: 0;"></button>

						<div class="collapse navbar-collapse" id="navbarDropdown">
							<ul class="navbar-nav mr-auto">
								<li class="nav-item active">
									<a class="nav-link" href="{{ route('index') }}"><img class="pixel mr-1" src="{{ asset('images') }}/home.png" style="width: 20px;" alt="Overall skill icon">Home <span class="sr-only">(current)</span></a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="{{ route('news') }}"><img class="pixel mr-1" src="{{ asset('images') }}/newspaper.png" style="width: 20px;" alt="News icon">News</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="{{ route('hiscore', ['skill', 'overall']) }}"><img class="pixel mr-1" src="{{ asset('images') }}/hiscore.png" style="width: 20px;" alt="Hiscores icon">Hiscores</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="#"><img class="pixel mr-1" src="{{ asset('images') }}/calendar.png" style="width: 20px;" alt="Calendar icon">Calendar</a>
								</li>
								<li class="nav-item">
									<a class="nav-link" href="{{ route('account') }}"><img class="pixel mr-1" src="{{ asset('images') }}/account.png" style="width: 20px;" alt="Accounts icon">Accounts</a>
								</li>
							</ul>

							<div class="mb-2 header-sword-texture"></div>

							<p class="text-light text-center"><img class="pixel mr-1" src="{{ asset('images') }}/watch.png" style="width: 20px;" alt="Watch icon">Next update: {{ Helper::roundToNextHour() }}<img class="pixel ml-1" src="{{ asset('images') }}/watch.png" style="width: 20px;" alt="Watch icon"></p>
						</div>
					</nav>

					@yield('additional-content')

					<div class="col-md-12 bg-dark text-light background-panel-texture py-3 mt-4">
						<h2 class="text-center header-sword-texture">Notifications</h2>
						<notification></notification>
					</div>

					<div class="mt-4 p-2 background-panel-texture">
						<iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
					</div>
				</div>

				<div class="col-md-9">
					@if ($errors->any())
						<div class="alert alert-danger col-md-4" style="margin: auto; margin-bottom: 1rem;">
							@foreach ($errors->all() as $errorMessage)
								<strong>Error!</strong> {{ $errorMessage }}<br>
							@endforeach
						</div>
					@endif

					@if (Session::has('message'))
						<div class="alert alert-success col-md-4" style="margin: auto; margin-bottom: 1rem;">
							<strong>Success!</strong> {{ Session::get('message') }}<br>
						</div>
					@endif

					@yield('content')
				</div>
			</div>
		</div>
	</div>

	<!-- Scripts -->
	<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
