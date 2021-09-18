@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
@section('navigation')
    <button type="button" class="btn btn-danger">
        <span>Ban user</span>
    </button>
@endsection

<div class="bg-admin-dark p-4">
    <div class="text-center">
        <img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png"
             class="pixel icon"
             alt="Profile icon"
             style="width: 10rem; height: 10rem;">

        <h1>{{ $user->name }}</h1>
        {{--<h3>@role('admin') [Admin] @endrole</h3>--}}
    </div>

    <div class="row">
        <div class="col"></div>
        <div class="col-12 col-md-7">
            <form method="POST" action="{{ route('admin-edit-user', $user) }}">
                @method('PATCH')
                @csrf

                <div class="row mb-3">
                    <label for="name" class="col-sm-3 col-form-label">Username</label>
                    <div class="col-sm-9">
                        <input type="text"
                               id="name"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ $user->name }}">
                    </div>
                </div>
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <div class="row mb-3">
                    <label for="name" class="col-sm-3 col-form-label">E-mail</label>
                    <div class="col-sm-9">
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ $user->email }}">
                    </div>
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <div class="row mb-3">
                    <label for="name" class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-9">
                        <div class="form-check">
                            <input type="radio"
                                   id="private"
                                   name="private"
                                   class="form-check-input"
                                   value="1"
                                   checked>
                            <label for="private" class="form-check-label">
                                Private
                            </label>
                        </div>
                        <div class="form-check">
                            <input type="radio"
                                   id="public"
                                   name="private"
                                   class="form-check-input"
                                   value="0">
                            <label for="public" class="form-check-label">
                                Public
                            </label>
                        </div>
                    </div>
                </div>
                @error('private')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                <div class="row mb-3">
                    <label for="name" class="col-sm-3 col-form-label">Profile icon ID</label>
                    <div class="col-sm-9">

                        <div class="input-group">
                            <span class="input-group-text">
                                <img
                                    src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png"
                                    class="pixel hiscore-icon"
                                    alt="Current profile icon">
                            </span>
                            <input type="number"
                                   id="icon_id"
                                   name="icon_id"
                                   class="form-control @error('icon_id') is-invalid @enderror"
                                   value="{{ $user->icon_id }}">
                        </div>
                        <div class="form-text">
                            Type in the ID of an in-game item you wish to display as the profile icon for this user.
                            <br>
                            Search item icons
                            <a href="https://www.osrsbox.com/tools/item-search/" class="btn-link" target="_blank" rel="noopener noreferrer">
                                here
                            </a>
                        </div>
                    </div>
                </div>
                @error('icon_id')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror

                @if (sizeof($user->account))
                    <h5 class="text-center">Transfer ownership of OSRS accounts:</h5>

                    @foreach ($user->account as $key => $account)
                        <div class="row mb-3">
                            <label for="account[{{ $account->id }}]"
                                   class="col-sm-3 col-form-label">
                                {{ $account->username }}
                            </label>
                            <div class="col-sm-9">
                                <input type="hidden"
                                       id="account[{{ $account->id }}]"
                                       name="accountId[{{ $account->id }}]"
                                       value="{{ $account->id }}">

                                <input type="text"
                                       id="account[{{ $account->id }}]"
                                       name="account[{{ $account->id }}]"
                                       class="form-control @error('account[{{ $account->id }}]') is-invalid @enderror"
                                       placeholder="Username or ID of new owner">

                                @error('account[{{ $account->id }}]')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        @php $key++ @endphp
                    @endforeach
                @endif

                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
        <div class="col"></div>
    </div>
</div>
@endsection
