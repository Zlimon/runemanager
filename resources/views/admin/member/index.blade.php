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
                   value="{{ old('search') }}" placeholder="{{ $members->random()->username }}" autofocus required>

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
    @foreach ($members as $member)
        <tr>
            <td>{{ $member->id }}</td>
            <td>{{ $member->username }}</td>
            <td>{{ number_format($member->rank) }}</td>
            <td>{{ $member->level }}</td>
            <td>{{ number_format($member->xp) }}</td>
            <td>@if ($member->user_id)<a
                    href="{{ route('admin-show-user', $member->user_id) }}">@if ($member->user->icon_id)<img
                        class="pixel"
                        src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $member->user->icon_id }}.png" width="54"
                        alt="Profile icon">@endif{{ $member->user_id }} - {{ $member->user->name }}</a>@endif</td>
            <td>{{ \Carbon\Carbon::parse($member->created_at)->format('d. M Y H:i') }}</td>
            <td><a class="btn btn-success mr-2" href="{{ route('admin-show-member', $member->id) }}">Show</a></td>
        </tr>
    @endforeach
</table>
@endsection
