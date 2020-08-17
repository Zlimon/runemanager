@extends('layouts.layout')

@section('title')
	{{ $account->username }}
@endsection

@section('content')
	@if ($account->user->private === 0 || $account->user->id == Auth::user()->id)
		@if ($account->user->icon_id != null)
			<div class="profile-icon">
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $account->user->icon_id }}.png" width="150" alt="Profile icon">
			</div>
		@elseif (Auth::check() && $account->user->icon_id == null && $account->user->id == Auth::user()->id)
			<div class="profile-icon">
				<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Helper::randomItemId() }}.png" width="150" alt="Profile icon">
				<p>Get your own profile icon <a href="{{ route('edit-user') }}">here</a>!</p>
			</div>
		@endif

		<div class="float-left ml-3">
			<h1 class="text-left">{{ $account->username }}</h1>

			<span>Rank: <strong>{{ number_format($account->rank) }}</strong></span>
			<br>
			<span>Total XP: <strong>{{ number_format($account->xp) }}</strong></span>
			<br>
			<span>Total Level: <strong>{{ $account->level }}</strong></span>
			<br>
			<span>Joined: <strong>{{ \Carbon\Carbon::parse($account->created_at)->format('d. M Y') }}</strong></span>
		</div>

		<table>
			<tr>
				<th></th>
				<th>Level</th>
				<th>XP</th>
				<th>Rank</th>
			</tr>
			@php
				$i = 0;
			@endphp
			@foreach ($stats as $skill)
				@foreach ($skill as $skillData)
					<tr>
						<td>
							<a href="{{ route('show-skill', $skills[$i]) }}">
								<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skills[$i]) }}.png" width="35px" alt="{{ ucfirst($skills[$i]) }} skill icon"/>
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
	@else
		<div class="text-center">
			<h1>This user is private</h1>
			<img class="pixel" src="{{ asset('images') }}/ignore.png" width="75px" alt="Sad face">
		</div>
	@endif
@endsection