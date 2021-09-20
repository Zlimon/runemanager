@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-4">
            <div class="p-4 bg-admin-dark">
                <h1>Reserve an OSRS account</h1>
                <p>If you want to reserve an Old School RuneScape account for future use / prevent it being claimed, you can do it here.</p>
                <p>To link the account to an user, visit the account page and fill the "Transfer ownership of this account" form.</p>

                <div class="p-4 bg-admin-info">
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
                            <label for="account" class="form-label">RuneManager username or ID</label>
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
            </div>
        </div>
    </div>
@endsection
