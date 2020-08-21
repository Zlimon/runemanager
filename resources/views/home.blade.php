@extends('layouts.layout')

@section('title')
	{{ Auth::user()->name }}
@endsection

@section('content')
	<div class="profile-icon">
		@if ($user->icon_id)
			<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="150" alt="Profile icon">
			<br>
			<span><a href="{{ route('edit-user') }}">Edit profile</a></span>
		@else
			<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Helper::randomItemId() }}.png" width="150" alt="Profile icon">
			<br>
			<span>Get your own profile icon <a href="{{ route('edit-user') }}">here</a>!</span>
		@endif
	</div>

	<div class="float-left ml-3">
		<h1>Welcome, {{ Auth::user()->name }}</h1>

		<p>RuneScape account: <strong>{{ $accounts[0]->username }}</strong></p>
		<p>Joined: <strong>{{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y') }}</strong></p>
	</div>

	<div class="float-right text-center">
		@if ($user->private === 0)
			<img class="align" src="{{ asset('images') }}/friend.png" alt="Friend icon" title="Currently not private">

			<p>Current status: <strong>Not private</strong></p>
		@else
			<img class="align" src="{{ asset('images') }}/ignore.png" alt="Ignore icon" title="Currently private">

			<p>Current status: <strong>Private</strong></p>
		@endif
	</div>

	@foreach ($accounts as $account)
		<h1 style="clear: both;">Personal scores for {{ $account->username }}</h1>
		<table>
			<tr>
				<th></th>
				<th>Level</th>
				<th>XP</th>
				<th>Rank</th>
			</tr>
			<tr>
				<td>
					<img class="align" src="{{ asset('images/skills/') }}/Overall.png" width="35px" alt="Overall skill icon">
					Overall
				</td>
				<td>{{ $account->level }}</td>
				<td>{{ number_format($account->xp) }}</td>
				<td>{{ number_format($account->rank) }}</td>
			</tr>

			@php
				$i = 0;
			@endphp
			@foreach ($stats[$account->username] as $skill)
				@foreach ($skill as $skillData)
					<tr>
						<td>
							<a href="{{ route('show-hiscore', $skills[$i]) }}">
								<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skills[$i]) }}.png" width="35px" alt="{{ ucfirst($skills[$i]) }} skill icon">
								{{ ucfirst($skills[$i]) }}
							</a>
						</td>
						<td>{{ $skillData->level }}</td>
						<td>{{ (number_format($skillData->xp) >= 1 ? number_format($skillData->xp) : "Unranked") }}</td>
						<td>{{ (number_format($skillData->rank) >= 1 ? number_format($skillData->rank) : "Unranked") }}</td>
					</tr>
				@endforeach
				@php
					$i++;
				@endphp
			@endforeach
		</table>
	@endforeach
@endsection