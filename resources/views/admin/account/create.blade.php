@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <h1>Register an OSRS account</h1>
    <p>If you want to preregister an OSRS account for future use / reference, you can do it here.</p>
    <p>This means this account will not be able to be linked for a new user, but have to be linked manually.</p>

    <form method="POST" action="{{ route('admin-create-account') }}">
        @csrf

        <label for="account" class="col-form-label text-md-right">RuneScape username</label>

        <input id="account" type="text" class="form-control col-3 @error('account') is-invalid @enderror"
               name="account" value="{{ old('account') }}" required autofocus>

        @error('account')
        <span class="invalid-feedback" role="alert">
				<strong>{{ $message }}</strong>
			</span>
        @enderror

        <label for="user" class="col-form-label text-md-right">RuneManager user username or ID</label>

        <input id="user" type="text" class="form-control col-3 @error('user') is-invalid @enderror"
               name="user" value="{{ old('user') }}">

        @error('user')
        <span class="invalid-feedback" role="alert">
				<strong>{{ $message }}</strong>
			</span>
        @enderror

        <div class="mt-2">
            <button type="submit" class="btn btn-primary">Register</button>
        </div>
    </form>
@endsection
