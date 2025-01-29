@extends('layouts.layout')

@section('title')
    Register
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
                <h1 class="text-center header-chatbox-sword">Register</h1>

                <div class="d-flex justify-content-center">
                    <div class="col-12 col-md-6">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="text-dark">
                                <div class="form-floating mb-3">
                                    <input type="text" id="name" name="name" class="form-control" placeholder="Username" required autofocus>
                                    <label for="name">Username</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="email" id="email" name="email" class="form-control" placeholder="Email address" required>
                                    <label for="email">Email address</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                                    <label for="password">Password</label>
                                </div>
                                <div class="form-floating mb-3">
                                    <input type="password" id="password-confirm" name="password-confirm" class="form-control" placeholder="Password">
                                    <label for="password-confirm">Password</label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
