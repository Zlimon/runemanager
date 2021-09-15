@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <div class="row mb-4">
        <div class="col-md-9">
            <accounthiscore :account="{{ $account }}"></accounthiscore>
        </div>

        <div class="col-md-3 text-center">
            <h5>Transfer ownership of this account:</h5>

            <form method="POST" action="{{ route('admin-update-account', $account) }}">
                @method('PATCH')
                @csrf

                @if ($account->user_id)
                    <label for="user">From <strong>{{ $account->user->name }}</strong> to:</label>
                @else
                    <label for="user"><em>Currently not linked to any user</em></label>
                @endif

                <input id="user" type="text" class="form-control @error('user') is-invalid @enderror"
                       name="user" value="{{ old('user') }}" placeholder="Username or ID of new owner">

                @error('user')
                <span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
                @enderror

                <button type="submit" class="btn btn-primary mt-2">Transfer</button>
            </form>
        </div>
    </div>
@endsection
