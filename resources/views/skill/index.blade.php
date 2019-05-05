@extends('layouts.layout')

@section('title')
	{{ __('title.skill') }}
@endsection

@section('content')
	<h1>{{ __('title.skill') }}</h1>

	@foreach ($skills as $skill)
		<a href="{{ route('show-skill', $skill) }}">
			<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skill) }}.png" alt="{{ ucfirst($skill) }} skill icon">
		</a>
	@endforeach
@endsection