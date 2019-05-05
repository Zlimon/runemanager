@extends('layouts.layout')

@section('title')
	| {{ __('title.index') }}
@endsection

@section('content')
	<div class="container">
		<div class="row no-gutters justify-content-center">
			<div class="col-md-7" style="min-width: 765px; max-width: 765px;">
				<div class="main-page">
					<div class="main-page-body" style="padding: 0; width: 100%;">
						<h1>Latest news and updates</h1>

						@foreach ($recentPosts as $post)
							<article class="latest-news">
								<div class="content">
									<a href="{{ route('show-newspost', $post->id) }}">
										<div class="date text-dark">
											<span class="month">May</span>
											<span class="day">{{ Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $post->created_at)->day }}</span>
										</div>
										<div class="title">
											<span>{{ $post->title }}</span>
										</div>
										<div class="meta">
											<span>{{ $post->user->name }} | {{ $post->category->category }}</span>
										</div>
										<p>{{ $post->shortstory }}</p>
									</a>
								</div>
								<span><strong><a href="{{ route('show-newspost', $post->id) }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a></strong></span>
							</article>
						@endforeach
					</div>
				</div>
			</div>

			<div class="col-md-4 ml-auto">
				<div class="text-light bg-dark p-4 mb-4" style="border-radius: 3px;">
					<h1 class="text-light">Welcome to {{ config('app.name', 'RuneManager') }}</h1>
					<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
				</div>

				<iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
			</div>
		</div>
	</div>
@endsection