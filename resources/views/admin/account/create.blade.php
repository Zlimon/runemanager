@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    <h1>Register an OSRS account</h1>
    <p>If you want to preregister an OSRS account for future use / reference, you can do it here.</p>
    <p>This means this account will not be able to be linked for a new user, but have to be linked manually.</p>

    <div class="bg-admin-dark p-4">
        <form method="POST" action="{{ route('admin-create-account') }}">
            @csrf

            <div class="mb-3">
                <label for="account" class="form-label">Old School RuneScape username</label>
                <input type="text"
                       id="account"
                       name="account"
                       class="form-control @error('account') is-invalid @enderror"
                       autofocus required>
            </div>
            @error('account')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <div class="mb-3">
                <label for="account" class="form-label">RuneManager user username or ID</label>
                <input type="text"
                       id="user"
                       name="user"
                       class="form-control @error('account') is-invalid @enderror"
                       required>
            </div>
            @error('user')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
@endsection
