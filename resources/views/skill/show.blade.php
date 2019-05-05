@extends('layouts.layout')

@section('title')
	{{ ucfirst($skillname) }} {{ __('title.hiscore') }}
@endsection

@section('content')
	<h1>
		<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skillname) }}.png" width="35px" alt="{{ ucfirst($skillname) }} skill icon">
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
					<a href="{{ route('show-member', $skill->account_id) }}">
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
@endsection