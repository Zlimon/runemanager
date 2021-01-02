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
    <link href="{{ asset('css/button.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<body>
<div id="app">
    <div class="container mt-2">
        <div class="row flex-column-reverse flex-md-row">
            <div class="col-md-3">
                <div class="col-md-12 bg-dark background-dialog-panel mb-3">
                    <nav class="col-md-12 navbar navbar-dark">
                        <h3 class="navbar-brand" href="#" data-toggle="collapse" data-target="#navbarDropdown"
                            aria-controls="navbarDropdown" aria-expanded="false"
                            aria-label="Toggle navigation">@yield('title')</h3>

                        <button class="navbar-toggler dropdown-bank-tag-down-arrow" type="button" data-toggle="collapse"
                                data-target="#navbarDropdown"
                                aria-controls="navbarDropdown" aria-expanded="false" aria-label="Toggle navigation"
                                style="border: 0;"></button>

                        <div class="collapse navbar-collapse" id="navbarDropdown">
                            <ul class="navbar-nav mr-auto">
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('news') }}">
                                        <img src="{{ asset('images/newspaper.png') }}"
                                             class="pixel mr-1"
                                             alt="Newspost page icon"
                                             title="Click here to read the newsposts"
                                             style="width: 1.5rem;">
                                        <span>News</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('hiscore', ['skill', 'overall']) }}">
                                        <img src="{{ asset('images/hiscore.png') }}"
                                             class="pixel mr-1"
                                             alt="Hiscores page icon"
                                             title="Click here to watch the hiscores"
                                             style="width: 1.5rem;">
                                        <span>Hiscores</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">
                                        <img src="{{ asset('images/calendar.png') }}"
                                             class="pixel mr-1"
                                             alt="Calendar page icon"
                                             title="Click here to visit the calendar"
                                             style="width: 1.5rem;">
                                        <span>Calendar</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('account') }}">
                                        <img src="{{ asset('images/account.png') }}"
                                             class="pixel mr-1"
                                             alt="Account page icon"
                                             title="Click here to browse through the registered accounts"
                                             style="width: 1.5rem;">
                                        <span>Accounts</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </nav>

                    <div class="mb-2 header-chatbox-sword"></div>

                    <nav class="col-md-12 navbar-dark">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="{{ route('index') }}">
                                    <img src="{{ asset('images/home.png') }}"
                                         class="pixel mr-1"
                                         alt="Home icon"
                                         title="Click here to go to the main page"
                                         style="width: 1.5rem;">
                                    <span>Home <span class="sr-only">(current)</span></span>
                                </a>
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
                                    <a class="nav-link" href="{{ route('home') }}">
                                        <img
                                            src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Auth::user()->icon_id }}.png"
                                            class="pixel mr-1"
                                            alt="Profile icon"
                                            title="Click here to visit your profile"
                                            style="width: 1.5rem;">
                                        <span>Profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <img src="{{ asset('images/logout_small.png') }}"
                                             class="pixel mr-1"
                                             alt="Log out icon"
                                             title="Click here to log out"
                                             style="width: 1.5rem;">
                                        {{ __('Log out') }}
                                    </a>
                                </li>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            @endguest
                        </ul>
                    </nav>

                    <div class="mb-2 header-chatbox-sword"></div>

                    <p class="text-light text-center pb-4">
                        <img src="{{ asset('images/watch.png') }}"
                             class="pixel mr-1"
                             alt="Watch icon"
                             style="width: 1.5rem;">
                        <span>Next update: {{ Helper::roundToNextHour() }}</span>
                        <img src="{{ asset('images/watch.png') }}"
                             class="pixel mr-1"
                             alt="Watch icon"
                             style="width: 1.5rem;">
                    </p>
                </div>

                @yield('additional-content')

                <div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mb-3">
                    <h2 class="text-center header-chatbox-sword">Notifications</h2>
                    <allnotification></allnotification>
                </div>

                <div class="col-md-12 p-2 background-dialog-panel mb-3">
                    <iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%"
                            height="500" allowtransparency="true" frameborder="0"></iframe>
                </div>
            </div>

            <div class="col-md-9">
                @if ($errors->any())
                    <div class="alert alert-danger bg-dark col-md-8 background-dialog-iron-rivets mb-1"
                         style="margin: auto; border: 0;">
                        @foreach ($errors->all() as $errorMessage)
                            <div class="row align-items-center">
                                <div class="col-3 col-sm-2 col-md-2">
                                    <img src="{{ asset('images/ignore.png') }}"
                                         class="pixel icon"
                                         alt="Sad face">
                                </div>
                                <div class="col">
                                    <h1 class="runescape-danger font-medium">Error!</h1>
                                    <p class="text-light">{{ $errorMessage }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (Session::has('message'))
                    <div class="alert alert-success bg-dark col-md-8 background-dialog-iron-rivets mb-1"
                         style="margin: auto; border: 0;">
                        <div class="row align-items-center">
                            <div class="col-3 col-sm-2 col-md-2">
                                <img src="{{ asset('images/friend.png') }}"
                                     class="pixel icon"
                                     alt="Happy face">
                            </div>
                            <div class="col">
                                <h1 class="runescape-success font-medium">Success!</h1>
                                <p class="text-light">{{ Session::get('message') }}</p>
                            </div>
                        </div>
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
