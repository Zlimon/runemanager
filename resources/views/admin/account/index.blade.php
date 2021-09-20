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
          class="row d-flex justify-content-center mb-3">
        @csrf

        <div class="col-12 col-md-4">
            <div class="row">
                <div class="input-group">
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control @error('search') is-invalid @enderror"
                           placeholder="{{ $accounts->random()->name }}"
                           autofocus required>
                    <button class="btn btn-primary">Search</button>
                </div>
            </div>
        </div>
    </form>

    @if ($query)<h3>Search results for "{{ $query }}"</h3>@endif
    <div class="table-responsive">
        <table class="table admin-table">
            <thead>
                <tr>
                    <th>Account ID</th>
                    <th>Username</th>
                    <th class="d-none d-md-table-cell">Rank</th>
                    <th class="d-none d-md-table-cell">Level</th>
                    <th class="d-none d-md-table-cell">XP</th>
                    <th>Linked user</th>
                    <th class="d-none d-md-table-cell">Registered</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($accounts as $account)
                    <tr>
                        <th scope="row">{{ $account->id }}</th>
                        <td>
                            @if ($account->account_type !== "normal")
                                <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                     alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                            @endif
                            {{ $account->username }}
                        </td>
                        <td class="d-none d-md-table-cell">{{ number_format($account->rank) }}</td>
                        <td class="d-none d-md-table-cell">{{ $account->level }}</td>
                        <td class="d-none d-md-table-cell">{{ number_format($account->xp) }}</td>
                        <td>
                            @if ($account->user_id)
                                <a href="{{ route('admin-show-user', $account->user_id) }}" class="link-primary">
                                    <div class="d-flex align-items-center">
                                        <div class="d-none d-md-table-cell">
                                            <img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png"
                                                 class="pixel"
                                                 alt="Profile icon"
                                                 width="35">
                                        </div>
                                        <span>{{ $account->user_id }} - {{ $account->user->name }}</span>
                                        @if ($account->user->private === 1)
                                            <img src="/storage/resource-pack/bank/placeholders_lock.png"
                                                 class="pixel"
                                                 alt="Padlock icon"
                                                 title="User is private"
                                                 width="20">
                                        @endif
                                    </div>
                                </a>
                            @endif
                        </td>
                        <td class="d-none d-md-table-cell">{{ \Carbon\Carbon::parse($account->created_at)->format('d. M Y H:i') }}</td>
                        <td><a class="btn btn-success mr-2" href="{{ route('admin-show-account', $account->username) }}">Show</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
