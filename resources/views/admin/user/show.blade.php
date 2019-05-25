@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	@section('navigation')
		<a class="btn btn-primary mr-2" href="{{ route('admin-edit-user', $user->id) }}">Edit</a>

		<button type="button" class="btn btn-danger">
			<span>Ban user</span>
		</button>
	@endsection

	<div class="row">
		<div class="col-md-3">
			<div class="bg-dark p-4">
				@if ($user->icon_id)
					<div class="text-center">
						<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $user->icon_id }}.png" width="100" alt="Profile icon">
					</div>
				@endif
				<h1>{{ $user->name }}</h1>
				<p><strong>rank</strong></p>

				<h2>About</h2>
				<p><strong>Email:</strong> {{ $user->email }}</p>
				<p><strong>Icon ID:</strong> {{ (is_null($user->icon_id)) ? 'None' : $user->icon_id }}</p>
				<p><strong>Private:</strong> {{ ($user->private === 0 ? 'False' : 'True') }}</p>
				<p><strong>User ID:</strong> {{ $user->id }}</p>
				<p><strong>Joined:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('d. M Y H:i') }}</p>
				<p><strong>Last updated:</strong> {{ \Carbon\Carbon::parse($user->updated_at)->format('d. M Y H:i') }}</p>
			</div>
		</div>

		<div class="col-md-9">
			<div class="bg-dark p-4">
				<h1>OSRS accounts</h1>

				@foreach ($accounts as $amount => $account)
					<div class="row">
						<div class="col-md-5">
							<div class="p-3 mb-4" style="background: #6d7fcc;">
								<h2>@if (count($user->member) > 1) {{ ++$amount }} - @endif{{ $account->username }}</h2>
								<p>
									<span><strong>Rank: </strong>{{ number_format($account->rank) }} |</span>
									<span><strong>Level: </strong>{{ $account->level }} |</span>
									<span><strong>Total XP:</strong> {{ number_format($account->xp) }}</span>
								</p>
							</div>
						</div>

						<div class="col-md-7 mb-4">
							<div class="p-3" style="background: #6d7fcc;">
								@foreach (Helper::accountStats($account->id) as $skillId => $skill)
									<a href="{{ route('show-skill', Helper::listSkills()[$skillId]) }}">
										<img class="pixel" src="{{ asset('images/skills/') }}/{{ ucfirst(Helper::listSkills()[$skillId]) }}.png" alt="{{ ucfirst(Helper::listSkills()[$skillId]) }} skill icon">
										
										<span>{{ json_decode($skill[0]->level, true) }}</span>
									</a>
								@endforeach
							</div>
						</div>
					</div>
				@endforeach
			</div>
		</div>
	</div>
@endsection