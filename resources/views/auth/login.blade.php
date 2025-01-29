@extends('layouts.layout')

@section('title')
    Login
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-3 d-none d-md-block">
            <div class="bg-dark background-dialog-panel p-3">
                <h2 class="text-center header-chatbox-sword">Notifications</h2>
                <announcementall></announcementall>
            </div>
        </div>

        <div class="col">
            <div class="bg-dark background-dialog-panel p-3">
                <h1 class="text-center header-chatbox-sword">Login</h1>

                <div class="d-flex justify-content-center">
                    <div class="col-12 col-md-6">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="text-dark">
                                <div class="form-floating mb-3">
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email address" required autofocus>
                                    <label for="email">Email address</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                                    <label for="password">Password</label>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <button type="submit" class="btn btn-primary me-3">Log in</button>

                                    <a href="{{ route('register') }}" class="btn btn-info">Register</a>
                                </div>

                                <a href="{{ route('password.request') }}">
                                    Forgot Your Password?
                                </a>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
