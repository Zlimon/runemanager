@extends('layouts.layout')

@section('title')
	| {{ ucfirst($skillname) }} {{ __('title.hiscore') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('hiscore') }}">{{ __('title.hiscore') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('skill') }}">{{ __('title.skill') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ ucfirst($skillname) }}</span>
					<span class="float-right">Next update: </span>
				</div>
				<div class="main-page-body">
					<h1>
						<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skillname) }}.png" width="35px" alt="{{ ucfirst($skillname) }} skill icon" />
						{{ ucfirst($skillname) }}
					</h1>

					<table>
						<tr>
							<th>Rank</th>
							<th>Username</th>
							<th>Level</th>
							<th>XP</th>
							<th>Hiscore Rank</th>
						</tr>

						@php
							$rankCounter = 1;
						@endphp
						@foreach ($hiscores as $skill)
							<tr>
								<td>{{ $rankCounter }}</td>
								<td>
									<a href="{{ route('show-member', $skill->username) }}">
										{{ $skill->username }}
									</a>
								</td>
								<td>{{ $skill->level }}</td>
								<td>{{ number_format($skill->xp) }}</td>
								<td>{{ number_format($skill->rank) }}</td>
							</tr>
							@php
								$rankCounter++;
							@endphp
						@endforeach
					</table>
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection