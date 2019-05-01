@extends('layouts.layout')

@section('title')
	| {{ Auth::user()->name }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.home') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					@if ($errors->any())
						<div class="alert alert-danger" style="text-align: center;">
							@foreach ($errors->all() as $errorMessage)
								<span>{{ $errorMessage }} Resolve this <a href="{{ route('create-member') }}">here</a></span>
							@endforeach
						</div>
					@else
						<div class="profile-icon">
							@if ($member->user->icon_id)
								<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ $member->user->icon_id }}.png" width="72" alt="Profile icon">
								<p><a href="{{ route('edit-user') }}">Edit profile</a></p>
							@else
								<img class="pixel" src="https://www.osrsbox.com/osrsbox-db/items-icons/{{ Helper::randomItemId() }}.png" width="72" alt="Profile icon">
								<p>Get your own profile icon <a href="{{ route('edit-user') }}">here</a>!</p>
							@endif
						</div>

						<h1>Welcome, {{ Auth::user()->name }}</h1>

						<p>RuneScape account: {{ $member->username }}</p>

						<h1>Your personal scores</h1>
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
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection