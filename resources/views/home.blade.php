@extends('layouts.layout')

@section('title')
	{{ Auth::user()->name }}
@endsection

@section('content')
	@if ($errors->any())
		<div class="alert alert-danger" style="text-align: center;">
			@foreach ($errors->all() as $errorMessage)
				<span>{{ $errorMessage }} Resolve this <a href="{{ route('create-member') }}">here</a></span>
			@endforeach
		</div>
	@else
		<div class="profile-icon">
			@if ($member->user->icon_id)
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $member->user->icon_id }}.png" width="150" alt="Profile icon">
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

			<p>RuneScape account: <strong>{{ $member->username }}</strong></p>
			<p>Joined: <strong>{{ \Carbon\Carbon::parse($member->created_at)->format('d. M Y') }}</strong></p>
		</div>

		<h1 style="clear: both;">Your personal scores</h1>
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
				<td>{{ $member->level }}</td>
				<td>{{ number_format($member->xp) }}</td>
				<td>{{ number_format($member->rank) }}</td>
			</tr>

			@php
				$i = 0;
			@endphp
			@foreach ($stats as $skill)
				@foreach ($skill as $skillData)
					<tr>
						<td>
							<a href="{{ route('show-skill', $skills[$i]) }}">
								<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skills[$i]) }}.png" width="35px" alt="{{ ucfirst($skills[$i]) }} skill icon">
								{{ ucfirst($skills[$i]) }}
							</a>
						</td>
						<td>{{ $skillData->level }}</td>
						<td>{{ number_format($skillData->xp) }}</td>
						<td>{{ number_format($skillData->rank) }}</td>
					</tr>
				@endforeach
				@php
					$i++;
				@endphp
			@endforeach
		</table>
	@endif
@endsection