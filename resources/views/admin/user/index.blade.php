@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
    <div class="content-body">
        <div class="text-center">
            <h1>Search for users</h1>
        </div>

        <form class="form-group row" method="POST" action="{{ route('admin-search-user') }}">
            @csrf

            <div class="col-md-3"></div>

            <div class="col-md-6 mb-2">
                <input id="search" type="text" class="form-control @error('search') is-invalid @enderror"
                       name="search" value="{{ old('search') }}" placeholder="{{ $users->random()->name }}" autofocus required>

                @error('search')
                <span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-search"></i></button>
        </form>
    </div>

    @if ($query)<h3>Search results for "{{ $query }}"</h3>@endif
    <table>
        <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Private</th>
            <th>Linked OSRS account</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td><img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="54" alt="Profile icon"> {{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>{{ ($user->private === 0 ? 'False' : 'True') }}</td>
                <td>
                    @foreach ($user->account as $account)
                        @if (count($user->account) > 1)
                            <p style="margin: 0;">
                                @if ($account->account_type !== "normal")
                                    <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                         alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                                @endif
                                <a href="{{ route('admin-show-member', $account->id) }}">
                                    {{ $account->id }} - {{ $account->username }}
                                </a>
                            </p>
                        @else
                            @if ($account->account_type !== "normal")
                                <img src="{{ asset('images/'.$account->account_type.'.png') }}"
                                     alt="{{ Helper::formatAccountTypeName($account->account_type) }} icon">
                            @endif
                            <a href="{{ route('admin-show-member', $account->id) }}">
                                {{ $account->id }} - {{ $account->username }}
                            </a>
                        @endif
                    @endforeach
                </td>
                <td>{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y H:i') }}</td>
                <td>
                    <a class="btn btn-success mr-2" href="{{ route('admin-show-user', $user->id) }}">Show</a>
                    <a class="btn btn-primary mr-2" href="{{ route('admin-edit-user', $user->id) }}">Edit</a>
                    <a class="btn btn-danger">Ban?</a>
                </td>
            </tr>
        @endforeach
    </table>
@endsection
