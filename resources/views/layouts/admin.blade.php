<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ Str::title(str_replace(['_', '-'], ' ', config('app.name', 'RuneManager'))) }} | @yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">

    <link href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          rel="stylesheet"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz"
          crossorigin="anonymous">
</head>

<body>
<div id="app">
    <div class="wrapper">
        <nav id="sidebar">
            <div class="p-4 text-center text-break bg-admin-info">
                <h3 style="margin: 0;">{{ Str::title(str_replace(['_', '-'], ' ', config('app.name', 'RuneManager'))) }}</h3>
            </div>

            <hr>

            <ul class="list-unstyled">
                <li class="@yield('active-dashboard')">
                    <a href="{{ route('admin-index') }}">Dashboard</a>
                </li>
                <li class="@yield('active-users')">
                    <a href="{{ route('admin-user') }}">Users</a>
                </li>
                <li class="@yield('active-accounts')">
                    <a href="{{ route('admin-account') }}">Accounts</a>
                </li>
                <li class="@yield('active-news')">
                    <a href="#news" class="dropdown-toggle" data-bs-toggle="collapse" role="button">
                        News
                    </a>
                    <ul class="collapse list-unstyled" id="news">
                        <li>
                            <a href="{{ route('admin-newspost') }}">All news</a>
                        </li>
                        <li>
                            <a href="{{ route('admin-newspost-create') }}">Post news</a>
                        </li>
                    </ul>
                </li>
                <li class="@yield('active-calendar')">
                    <a href="{{ route('admin-calendar') }}">Calendar</a>
                </li>
                <li class="@yield('active-settings')">
                    <a href="#settings" class="dropdown-toggle" data-bs-toggle="collapse" role="button"
                       aria-expanded="false">
                        Settings
                    </a>
                    <ul class="collapse list-unstyled" id="settings">
                        <li>
                            <a href="{{ route('admin-settings') }}">All Settings</a>
                        </li>
                        <li>
                            <a href="{{ route('admin-settings-resourcepack') }}">Resource Packs</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <hr>

            <ul class="mx-4 list-unstyled">
                <li>
                    <a href="{{ route('index') }}" class="btn bg-admin-info text-light">
                        Back to {{ Str::title(str_replace(['_', '-'], ' ', config('app.name', 'RuneManager'))) }}
                    </a>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-dark">
                <div class="container-fluid">
                    <div class="p-2">
                        <button class="btn bg-admin-info">
                            <i class="fas fa-align-left"></i><span>Toggle Sidebar</span>
                        </button>
                    </div>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        @yield('navigation')
                    </div>
                </div>
            </nav>

            <main class="p-2 p-md-4">
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
            </main>
        </div>
    </div>
</div>
</body>
</html>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
