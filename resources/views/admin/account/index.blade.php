@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-accounts')
    active
@endsection

@section('content')
    @section('navigation')
        <div class="p-2">
            <a class="btn btn-success" href="{{ route('admin-create-account') }}">Register account</a>
        </div>
    @endsection

    <div class="text-center">
        <h1>Search for accounts</h1>
    </div>

    <form method="POST" action="{{ route('admin-search-account') }}"
          class="row d-flex justify-content-center align-items-center">
        @csrf

        <div class="col-md-8">
            <div class="search">
                <i class="fa fa-search"></i>
                <input type="text"
                       id="search"
                       name="search"
                       class="form-control @error('search') is-invalid @enderror"
                       placeholder="{{ $accounts->random()->username }}"
                       autofocus required>
                <button class="btn btn-primary">Search</button>
            </div>

            @error('search')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
    </form>

    @if ($query)<h3>Search results for "{{ $query }}"</h3>@endif
    <table>
        <tr>
            <th>Account ID</th>
            <th>Username</th>
            <th>Rank</th>
            <th>Level</th>
            <th>XP</th>
            <th>Linked user</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
        @foreach ($accounts as $account)
            <tr>
                <td>{{ $account->id }}</td>
                <td>
                    @if ($account->account_type !== "normal")
                        <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                             alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                    @endif
                    {{ $account->username }}
                </td>
                <td>{{ number_format($account->rank) }}</td>
                <td>{{ $account->level }}</td>
                <td>{{ number_format($account->xp) }}</td>
                <td>
                    @if ($account->user_id)
                        <a href="{{ route('admin-show-user', $account->user_id) }}">
                            <img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png"
                                 class="pixel"
                                 alt="Profile icon"
                                 width="54">
                            {{ $account->user_id }} - {{ $account->user->name }}
                        </a>
                    @endif
                </td>
                <td>{{ \Carbon\Carbon::parse($account->created_at)->format('d. M Y H:i') }}</td>
                <td><a class="btn btn-success mr-2" href="{{ route('admin-show-account', $account->username) }}">Show</a></td>
            </tr>
        @endforeach
    </table>
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
        border: 2px solid blue;
    }

    .search .fa-search {
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
