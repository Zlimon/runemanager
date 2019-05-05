@extends('layouts.layout')

@section('title')
	| {{ __('title.update-log') }}
@endsection

@section('content')
	<div class="container">
		<div class="row justify-content-center">
			<div class="top"></div>
			<div class="main-page">
				<div class="main-page-header">
					<span><a href="{{ route('index') }}">{{ __('title.index') }}</a> <i class="fas fa-long-arrow-alt-right"></i> {{ __('title.update-log') }}</span>
					<span class="float-right">Next update: {{ Helper::roundToNextHour() }}</span>
				</div>
				<div class="main-page-body">
					<h1>{{ $post->title }}</h1>
					<p class="text-center">{{ $post->shortstory }}</p>
					{!! $post->longstory !!}
				</div>
			</div>
			<div class="bottom"></div>
		</div>
	</div>
@endsection