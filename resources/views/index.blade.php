@extends('layouts.layout')

@section('title')
	| {{ __('title.index') }}
@endsection

@section('content')
	<div class="container">
		<div class="row">
			<div class="col" style="width: 75%;">
				<div class="top"></div>
				<div class="main-page">
					<div class="main-page-body">
						<h1>Latest news</h1>

						@for ($i = 0; $i < 5; $i++)
							<article class="news-latest-box">
								<a href="">
									<div class="news-latest-date text-dark">
										<span class="news-latest-date-month">May</span>
										<span class="news-latest-date-date">4</span>
									</div>
									<div class="news-latest-title">
										<span><strong>Habski Hotel is now in english!</strong></span>
										<div class="triangle-right"></div>
									</div>
									<div class="news-latest-title-meta">
										<span>Zlimon | Category</span>
									</div>
									<span>Velkommen til Habski Hotel! Habski er fortsatt under vedlikehold!</span>
								</a>
							</article>
						@endfor
					</div>
				</div>
				<div class="bottom"></div>
			</div>

			<div class="col" style="width: 25%;">
				<div class="text-light bg-dark p-4 mb-4">
					<h1 class="text-light">Welcome to {{ config('app.name', 'RuneManager') }}</h1>
					<p>"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
				</div>

				<iframe src="https://discordapp.com/widget?id=351850127209660416&theme=dark" width="100%" height="500" allowtransparency="true" frameborder="0"></iframe>
			</div>
		</div>
	</div>
@endsection