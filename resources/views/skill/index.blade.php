@extends('layouts.layout')

@section('title')
	| {{ __('title.skill') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> <a href="{{ route('hiscore') }}">{{ __('title.hiscore') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.skill') }}</span>
					<span class="float-right">Next update: </span>
				</div>
				<div class="main-page-body">
					<h1>{{ __('title.skill') }}</h1>

					@foreach ($skills as $skill)
						<a href="{{ route('show-skill', $skill) }}">
							<img class="align" src="{{ asset('images/skills/') }}/{{ ucfirst($skill) }}.png" alt="{{ ucfirst($skill) }} skill icon">
						</a>
					@endforeach
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection