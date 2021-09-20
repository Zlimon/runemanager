@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('active-users')
    active
@endsection

@section('content')
    <div class="text-center">
        <h1>Search for users</h1>
    </div>

    <form method="POST" action="{{ route('admin-search-user') }}"
          class="row d-flex justify-content-center mb-3">
        @csrf

        <div class="col-12 col-md-4">
            <div class="row">
                <div class="input-group">
                    <input type="text"
                           id="search"
                           name="search"
                           class="form-control @error('search') is-invalid @enderror"
                           placeholder="{{ $users->random()->name }}"
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
                    <th>User ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Linked OSRS account</th>
                    <th class="d-none d-md-table-cell">Registered</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <th scope="row">{{ $user->id }}</th>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="d-none d-md-table-cell">
                                    <img src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png"
                                         class="pixel"
                                         alt="Profile icon"
                                         width="35">
                                </div>
                                <span>{{ $user->name }}</span>
                                @if ($user->private === 1)
                                    <img src="/storage/resource-pack/bank/placeholders_lock.png"
                                         class="pixel"
                                         alt="Padlock icon"
                                         title="User is private"
                                         width="20">
                                @endif
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach ($user->account as $account)
                                <a href="{{ route('admin-show-account', $account->username) }}" class="link-primary">
                                    @if ($account->account_type !== "normal")
                                        <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                             alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                                    @endif
                                    <span>{{ $account->id }} - {{ $account->username }}</span>
                                </a>
                                <br>
                            @endforeach
                        </td>
                        <td class="d-none d-md-table-cell">{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y H:i') }}</td>
                        <td>
                            <a class="btn btn-success mr-2" href="{{ route('admin-show-user', $user->id) }}">Show</a>
                            <a class="btn btn-primary mr-2" href="{{ route('admin-edit-user', $user->id) }}">Edit</a>
                            <a class="btn btn-danger">Ban</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
