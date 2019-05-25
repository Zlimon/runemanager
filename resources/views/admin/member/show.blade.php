@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	<div class="row mb-4">
		<div class="col-md-9">
			<h1>{{ $member->username }}</h1>

			<span>Rank: <strong>{{ number_format($member->rank) }}</strong></span>
			<br>
			<span>Total XP: <strong>{{ number_format($member->xp) }}</strong></span>
			<br>
			<span>Total Level: <strong>{{ $member->level }}</strong></span>
			<br>
			<span>Joined: <strong>{{ \Carbon\Carbon::parse($member->created_at)->format('d. M Y') }}</strong></span>
		</div>

		<div class="col-md-3 text-center">
			<h5>Transfer ownership of this account:</h5>

			<form method="POST" action="{{ route('admin-show-member', $member->id) }}">
				@method('PATCH')
				@csrf
				
				@if ($member->user_id)
					<label for="user_id">From <strong>{{ $member->user->name }}</strong> to:</label>
				@else
					<label for="user_id"><em>Currently not linked to any user</em></label>
				@endif
				
				<input id="user_id" type="text" class="form-control @error('user_id') is-invalid @enderror" name="user_id" value="{{ old('user_id') }}" placeholder="Username or ID of new owner">

				@error('user_id')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror

				<button type="submit" class="btn btn-primary mt-2">Transfer</button>
			</form>
		</div>
	</div>

	<table>
		<tr>
			<th>Skill</th>
			<th>Level</th>
			<th>XP</th>
			<th>Rank</th>
			<th>Last trained</th>
		</tr>
		@foreach (Helper::accountStats($member->id) as $skillId => $skill)
			<tr>
				<td>
					<a href="{{ route('show-skill', Helper::listSkills()[$skillId]) }}">
						<img src="{{ asset('images/skills/') }}/{{ ucfirst(Helper::listSkills()[$skillId]) }}.png" width="35px" alt="{{ ucfirst(Helper::listSkills()[$skillId]) }} skill icon">
						{{ ucfirst(Helper::listSkills()[$skillId]) }}
					</a>
				</td>
				<td>{{ $skill[0]->level }}</td>
				<td>{{ number_format($skill[0]->xp) }}</td>
				<td>{{ number_format($skill[0]->rank) }}</td>
				<td>{{ \Carbon\Carbon::parse($skill[0]->updated_at)->format('d. M Y H:i') }}</td>
			</tr>
		@endforeach
	</table>
@endsection