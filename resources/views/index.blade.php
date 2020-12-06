@extends('layouts.layout')

@section('title')
	Home
@endsection

@section('content')
	<link href="{{ asset('css/index.css') }}" rel="stylesheet">

	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3">
		<div class="row">
			<div class="col-md-10 align-self-center">
				<h1>Welcome to {{ config('app.name', 'RuneManager') }}</h1>
				<span>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</span>
			</div>

			<div class="col-md-2 align-self-center">
				<a href="{{ route('login') }}">
					<div class="btn btn-block button-combat-style-narrow">
						<span>Log in</span>
					</div>
				</a>

				<a href="{{ route('register') }}">
					<div class="btn btn-block button-combat-style-narrow">
						<span>Register</span>
					</div>
				</a>
			</div>
		</div>
	</div>

	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mt-4">
		<div class="col-md-12">
			<div class="row justify-content-between">
				<div class="col-md-3">
					<a href="{{ route('hiscore', ['skill', 'overall']) }}">
						<div class="btn btn-block button-combat-style-thin">
							<span>Hiscores</span>
						</div>
					</a>
				</div>

				<div class="col-md-3">
					<a href="#">
						<div class="btn btn-block button-combat-style-thin">
							<span>Calendar</span>
						</div>
					</a>
				</div>

				<div class="col-md-3">
					<a href="{{ route('account') }}">
						<div class="btn btn-block button-combat-style-thin">
							<span>Account</span>
						</div>
					</a>
				</div>
			</div>
		</div>

		<div class="col-md-12">
			<div class="row justify-content-around">
				<div class="hiscore-button-parent">
					<div class="hiscore-button bg-one">
						<a href="{{ route('hiscore', ['skill', 'overall']) }}">Skills</a>
					</div>
				</div>

				<div class="hiscore-button-parent right">
					<div class="hiscore-button bg-two">
						<a href="{{ route('hiscore', ['boss', 'vorkath']) }}">Bosses</a>
					</div>
				</div>

				<div class="hiscore-button-parent">
					<div class="hiscore-button bg-three">
						<a href="#">Clues</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-12 bg-dark text-light background-dialog-panel py-3 mt-4">
		<h2 class="text-center header-chatbox-sword">Latest news and updates</h2>

		@forelse ($recentPosts as $post)
			<article class="col-md-12 latest-news mt-4 pt-4 background-dialog-iron-rivets">
				<div class="row">
					<div class="col-4">
						<div class="image">
							<a href="{{ route('news-show', $post->id) }}">
								<img class="middle-image" src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image">
							</a>
						</div>
					</div>
					<div class="col-6">
						<div class="title">
							<span><a href="{{ route('news-show', $post->id) }}">{{ $post->title }}</a></span>
						</div>
						<div class="meta">
							<p>{{ $post->user->name }} | {{ $post->category->category }}</p>
						</div>
						<div class="shortstory">
							<span>{{ $post->shortstory }}</span>
							<p><strong><a href="{{ route('news-show', $post->id) }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a></strong></p>
						</div>
					</div>
					<div class="col-2">
						<div class="date">
							<span class="month">{{ \Carbon\Carbon::parse($post->created_at)->format('M') }}</span>
							<br>
							<span class="day">{{ \Carbon\Carbon::parse($post->created_at)->format('d') }}</span>
						</div>
					</div>
				</div>
			</article>
		@empty
			<p>test</p>
		@endforelse

		@if (count($recentPosts) > 0)
			<a class="btn button-combat-style-narrow mt-3" href="{{ route('news') }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a>
		@endif
	</div>
@endsection