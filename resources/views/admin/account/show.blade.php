@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col-12 col-md-3 mb-2">
            <div class="bg-admin-dark p-4">
                <h1>Skills</h1>

                <div class="p-4 mb-4 bg-admin-info">
                    <accountskillhiscore :account="{{ $account }}"></accountskillhiscore>
                </div>

                <h1>Bosses</h1>

                <div class="p-4 bg-admin-info">
                    <accountbosshiscore :account="{{ $account }}"></accountbosshiscore>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="bg-admin-dark p-4">
                <h5>Transfer ownership of this account</h5>

                <form method="POST" action="{{ route('admin-update-account', $account) }}">
                    @method('PATCH')
                    @csrf

                    @if ($account->user_id)
                        <label for="user" class="form-label">From <strong>{{ $account->user->name }}</strong> to:</label>
                    @else
                        <label for="user" class="form-label"><em>Currently not linked to any user</em></label>
                    @endif
                    <div class="row">
                        <div class="input-group">
                            <input type="text"
                                   id="user"
                                   name="user"
                                   class="form-control @error('user') is-invalid @enderror"
                                   placeholder="Username or ID of new owner"
                                   required>
                            <button class="btn btn-primary">Transfer</button>
                        </div>
                    </div>

                    @error('user')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </form>
            </div>
        </div>
    </div>
@endsection
