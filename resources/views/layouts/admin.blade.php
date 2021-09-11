<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'RuneManager') }} | @yield('title')</title>

    <!-- Styles -->
    <link href="{{ asset('css/button.css') }}" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">

    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css"
          integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
</head>
<body>
<div id="app">
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>{{ config('app.name', 'RuneManager') }}</h3>
            </div>

            <ul class="list-unstyled components">
                <p>Admin Panel</p>
                <li class="active">
                    <a href="{{ route('admin-index') }}">Dashboard</a>
                <!-- <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Dashboard</a>
                        <ul class="collapse list-unstyled" id="homeSubmenu">
                            <li>
                                <a href="{{ route('admin-index') }}">Home</a>
                            </li>
                        </ul> -->
                </li>
                <li>
                    <a href="{{ route('admin-user') }}">Users</a>
                </li>
                <li>
                    <a href="{{ route('admin-account') }}">Accounts</a>
                </li>
                <li>
                    <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">News</a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="{{ route('admin-news') }}">All news</a>
                        </li>
                        <li>
                            <a href="{{ route('admin-create-newspost') }}">Post news</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="{{ route('admin-calendar') }}">Calendar</a>
                </li>
                <li>
                    <a href="{{ route('admin-settings') }}">Settings</a>
                </li>
                <!-- <li>
                    <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Pages</a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="#">Page 1</a>
                        </li>
                        <li>
                            <a href="#">Page 2</a>
                        </li>
                        <li>
                            <a href="#">Page 3</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">Portfolio</a>
                </li>
                <li>
                    <a href="#">Contact</a>
                </li> -->
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="{{ route('index') }}" class="article">Back to {{ config('app.name', 'RuneManager') }}</a>
                </li>
            </ul>
        </nav>

        <div id="content">
            <nav class="navbar navbar-expand-lg text-light" style="background: #47535F;">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info mr-4">
                        <i class="fas fa-align-left"></i><span>Toggle Sidebar</span>
                    </button>
                    <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse"
                            data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <i class="fas fa-align-justify"></i>
                    </button>

                    <button type="button" class="btn btn-primary mr-2" onclick="back()">
                        <span>Back</span>
                    </button>

                    @yield('navigation')

                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <!-- <ul class="nav navbar-nav ml-auto">
                            <li class="nav-item active">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#">Page</a>
                            </li>
                        </ul> -->
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

                @yield('content')
            </main>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
