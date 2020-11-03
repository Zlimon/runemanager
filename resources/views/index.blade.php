@extends('layouts.layout')

@section('title')
	{{ __('title.update-log') }}
@endsection

@section('content')
			<h1>Latest news and updates</h1>

			@foreach ($recentPosts as $post)
				<article class="latest-news">

					<div class="row">
						<div class="col-sm-4">
							<div class="image">
								<a href="{{ route('show-newspost', $post->id) }}">
									<img class="middle-image" src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image">
								</a>
							</div>
						</div>
						<div class="col-sm-7">
							<div class="title">
								<span><a href="{{ route('show-newspost', $post->id) }}">{{ $post->title }}</a></span>
							</div>
							<div class="meta">
								<span>{{ $post->user->name }} | {{ $post->category->category }}</span>
							</div>
							<div class="shortstory">
								<p>{{ $post->shortstory }}</p>
								<p><strong><a href="{{ route('show-newspost', $post->id) }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a></strong></p>
							</div>
						</div>
						<div class="col-sm-1">
							<div class="date text-dark">
								<span class="month">{{ \Carbon\Carbon::parse($post->created_at)->format('M') }}</span>
								<span class="day">{{ \Carbon\Carbon::parse($post->created_at)->format('d') }}</span>
							</div>
						</div>
					</div>
				</article>
			@endforeach
			<div class="mt-5">
				<a class="btn btn-primary text-white float-right" style="margin-top: -30px;" href="{{ route('news') }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a>
			</div>
		</div>
	</div>

	<div class="col-md-4 ml-auto">
		<div class="text-light bg-dark p-4 mb-4" style="border-radius: 3px;">
			<h1 class="text-light">Welcome to {{ config('app.name', 'RuneManager') }}</h1>
			<p><strong>URL:</strong> http://runemanager.habski.me</p>
		</div>

		<iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
	</div>
@endsection