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
        <nav class="navbar sticky-top navbar-expand-md navbar-dark bg-dark navbar-laravel">
            <div class="container">
                <a class="navbar-brand" href="{{ route('index') }}">
                    {{ config('app.name', 'RuneManager') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('index') }}">{{ __('title.index') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('news') }}">{{ __('title.news') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('show-hiscore', 'overall') }}">{{ __('title.hiscore') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('account') }}">{{ __('title.member') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('task') }}">{{ __('title.task') }}</a>
                        </li>
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link">Next update: {{ Helper::roundToNextHour() }}</a>
                        </li>
                        <!-- Authentication Links -->
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
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('home') }}">{{ __('title.home') }}</a>
                                    <a class="dropdown-item" href="{{ route('edit-user') }}">{{ __('title.edit-member') }}</a>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <main class="py-4">
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

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-8">
                        <div class="main-page col-12 p-5">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>