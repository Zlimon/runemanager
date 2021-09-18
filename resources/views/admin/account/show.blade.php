@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    <div class="row">
        <div class="col-md-5">
            <div class="bg-admin-dark p-4">
                <div class="p-4 mb-4 bg-admin-info">
                    <h1>Skills</h1>

                    <accountskillhiscore :account="{{ $account }}"></accountskillhiscore>
                </div>

                <div class="p-4 bg-admin-info">
                    <h1>Bosses</h1>

                    <accountbosshiscore :account="{{ $account }}"></accountbosshiscore>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="bg-admin-dark p-4">
                <h5>Transfer ownership of this account</h5>

                <form method="POST" action="{{ route('admin-update-account', $account) }}">
                    @method('PATCH')
                    @csrf

                    @if ($account->user_id)
                        <label for="user">From <strong>{{ $account->user->name }}</strong> to:</label>
                    @else
                        <label for="user"><em>Currently not linked to any user</em></label>
                    @endif
                    <div class="search">
                        <i class="fas fa-random"></i>
                        <input type="text"
                               id="user"
                               name="user"
                               class="form-control @error('user') is-invalid @enderror"
                               placeholder="Username or ID of new owner"
                               autofocus required>
                        <button class="btn btn-primary">Transfer</button>
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

<style>
    .search {
        position: relative;
        box-shadow: 0 0 40px rgba(51, 51, 51, .1);
    }

    .search input {
        height: 60px;
        text-indent: 25px;
        border: 2px solid #d6d4d4;
    }

    .search input:focus {
        box-shadow: none;
    }

    .search .fa-random {
        position: absolute;
        top: 20px;
        left: 16px;
        color: black;
    }

    .search button {
        position: absolute;
        top: 5px;
        right: 5px;
        height: 50px;
        width: 110px;
    }
</style>
