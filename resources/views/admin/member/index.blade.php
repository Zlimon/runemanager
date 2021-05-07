@extends('layouts.admin')

@section('title')
    TITLE
@endsection

@section('content')
@section('navigation')
    <a class="btn btn-success" href="{{ route('admin-create-member') }}">Register account</a>
@endsection

<div class="content-body">
    <div class="text-center">
        <h1>Search for OSRS accounts</h1>
    </div>

    <form class="form-group row" method="POST" action="{{ route('admin-search-member') }}">
        @csrf

        <div class="col-md-3"></div>

        <div class="col-md-6 mb-2">
            <input id="search" type="text" class="form-control @error('search') is-invalid @enderror" name="search"
                   value="{{ old('search') }}" placeholder="{{ $accounts->random()->username }}" autofocus required>

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
            <td>@if ($account->user_id)<a
                    href="{{ route('admin-show-user', $account->user_id) }}">@if ($account->user->icon_id)<img
                        class="pixel"
                        src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png" width="54"
                        alt="Profile icon">@endif{{ $account->user_id }} - {{ $account->user->name }}</a>@endif</td>
            <td>{{ \Carbon\Carbon::parse($account->created_at)->format('d. M Y H:i') }}</td>
            <td><a class="btn btn-success mr-2" href="{{ route('admin-show-member', $account->username) }}">Show</a></td>
        </tr>
    @endforeach
</table>
@endsection
