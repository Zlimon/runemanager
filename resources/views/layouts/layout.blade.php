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
		<div class="container mt-2">
			<div class="row flex-column-reverse flex-md-row">
				<div class="col-md-3">
					<div class="col-md-12 bg-dark background-dialog-panel mb-3">
						<nav class="col-md-12 navbar navbar-dark">
							<a class="navbar-brand runescape-large" href="#" data-toggle="collapse" data-target="#navbarDropdown"
							aria-controls="navbarDropdown" aria-expanded="false" aria-label="Toggle navigation">@yield('title')</a>

							<button class="navbar-toggler dropdown-bank-tag-down-arrow" type="button" data-toggle="collapse" data-target="#navbarDropdown"
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
							</div>
						</nav>

						<div class="mb-2 header-chatbox-sword"></div>

						<ul class="col-md-12 navbar-nav mr-auto">
							<div class="col-md-12">
							<li class="nav-item active">
								<a class="nav-link" href="{{ route('index') }}"><img class="pixel mr-1" src="{{ asset('images') }}/home.png" style="width: 20px;" alt="Overall skill icon">Home <span class="sr-only">(current)</span></a>
							</li>
							@guest
	                            <li class="nav-item">
	                            	<a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
	                            </li>
	                            @if (Route::has('register'))
	                                <li class="nav-item">
	                                	<a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
	                                </li>
	                            @endif
	                        @else
	                        	<li class="nav-item">
	                        		<a class="nav-link" href="{{ route('home') }}"><img class="pixel mr-1" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Auth::user()->icon_id }}.png" alt="Profile icon" style="width: 20px;">{{ Auth::user()->name }}</a>
	                        	</li>
								<li class="nav-item">
									<a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
		                             	<img class="pixel mr-1" src="{{ asset('images') }}/logout_small.png" style="width: 20px;" alt="Accounts icon">
		                                {{ __('Log out') }}
		                            </a>
		                        </li>

	                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
	                                @csrf
	                            </form>
	                        @endguest
	                    </div>
	                    </ul>

						<div class="mb-2 header-chatbox-sword"></div>

						<p class="text-light text-center pb-4"><img class="pixel mr-1" src="{{ asset('images') }}/watch.png" style="width: 20px;" alt="Watch icon">Next update: {{ Helper::roundToNextHour() }}<img class="pixel ml-1" src="{{ asset('images') }}/watch.png" style="width: 20px;" alt="Watch icon"></p>
					</div>

					@yield('additional-content')

					<div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
						<h2 class="text-center header-chatbox-sword">Notifications</h2>
						<notification></notification>
					</div>

					<div class="col-md-12 p-2 background-dialog-panel mb-3">
						<iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
					</div>
				</div>

				<div class="col-md-9">
					@if ($errors->any())
						<div class="alert alert-danger bg-dark col-md-8 background-dialog-iron-rivets mb-1" style="margin: auto; border: 0;">
							@foreach ($errors->all() as $errorMessage)
								<h1 class="runescape-danger font-medium">Error!</h1>
								<p>{{ $errorMessage }}</p>
							@endforeach
						</div>
					@endif

					@if (Session::has('message'))
						<div class="alert alert-success bg-dark col-md-8 background-dialog-iron-rivets mb-1" style="margin: auto; border: 0;">
							<h1 class="runescape-success font-medium">Success!</h1>
							<p>{{ Session::get('message') }}</p>
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
