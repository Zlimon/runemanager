@extends('layouts.layout')

@section('title')
	| {{ __('title.hiscore') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.hiscore') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1 class="float-left">{{ __('title.hiscore') }}</h1>
					<span class="float-right"><a href="{{ route('skill') }}"><img class="align" src="{{ asset('images/skills/') }}/Overall.png" width="35px" alt="Overall skill icon"> {{ __('title.skill') }}</a></span>

					<table>
						<tr>
							<th>Rank</th>
							<th>Member</th>
							<th>Total Level</th>
							<th>Total XP</th>
							<th>Hiscore Rank</th>
						</tr>
						@php
							$rankCounter = 1;
						@endphp
						@foreach ($members as $member)
							<tr>
								<td>{{ $rankCounter }}</td>
								<td><a href="{{ route('show-member', $member->id) }}">{{ $member->username }}</a></td>
								<td>{{ $member->level }}</td>
								<td>{{ number_format($member->xp) }}</td>
								<td>{{ number_format($member->rank) }}</td>
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