@extends('layouts.layout')

@section('title')
	{{ __('title.hiscore') }}
@endsection

@section('content')
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
@endsection