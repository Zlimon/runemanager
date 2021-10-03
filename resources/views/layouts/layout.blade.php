<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RuneManager') }} | @yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}?ver={{ \App\Helpers\SettingHelper::getSetting('site_hash') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}?ver={{ \App\Helpers\SettingHelper::getSetting('site_hash') }}" rel="stylesheet">
    <link href="{{ asset('css/resource-pack.css') }}?ver={{ \App\Helpers\SettingHelper::getSetting('site_hash') }}" rel="stylesheet">
    <link href="{{ asset('css/button.css') }}?ver={{ \App\Helpers\SettingHelper::getSetting('site_hash') }}" rel="stylesheet">

    <link href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          rel="stylesheet"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz"
          crossorigin="anonymous">
</head>

<body>
    <main id="app">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark background-dialog-panel">
            <div class="container">
                <a class="navbar-brand" href="#">@yield('title')</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('index') }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/8013.png"
                                     class="pixel"
                                     alt="Home icon"
                                     title="Click here to go to the main page">
                                <span>Home</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('news') }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/11169.png"
                                     class="pixel"
                                     alt="Newspost page icon"
                                     title="Click here to read the newsposts">
                                <span>News</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('hiscore', ['skill', 'total']) }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/25045.png"
                                     class="pixel"
                                     alt="Hiscores page icon"
                                     title="Click here to watch the hiscores">
                                <span>Hiscores</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('calendar') }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/9649.png"
                                     class="pixel"
                                     alt="Calendar page icon"
                                     title="Click here to visit the calendar">
                                <span>Calendar</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('account') }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/1149.png"
                                     class="pixel"
                                     alt="Account page icon"
                                     title="Click here to browse through the registered accounts">
                                <span>Accounts</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('group') }}">
                                <img src="https://www.osrsbox.com/osrsbox-db/items-icons/12810.png"
                                     class="pixel"
                                     alt="Group page icon"
                                     title="Click here to browse through the registered groups">
                                <span>Groups</span>
                            </a>
                        </li>
                    </ul>

                    <div class="d-block d-md-none header-chatbox-sword"></div>

                    <ul class="navbar-nav ms-auto">
                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Log in</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('home') }}">
                                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Auth::user()->icon_id }}.png"
                                         class="pixel"
                                         alt="Profile icon"
                                         title="Click here to visit your profile">
                                    <span>Hello, {{ Auth::user()->name }}</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin-index') }}">
                                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/1.png"
                                         class="pixel"
                                         alt="Admin Panel icon"
                                         title="Click here to visit the Admin Panel">
                                    <span>Admin</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/8168.png"
                                         class="pixel"
                                         alt="Log out icon"
                                         title="Click here to log out">
                                    <span>Log out</span>
                                </a>
                            </li>

                            <form method="POST" action="{{ route('logout') }}" class="d-none" id="logout-form">
                                @csrf
                            </form>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container text-light" style="margin-top: 5rem;">
            @yield('content')
        </div>
    </main>
</body>
</html>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
