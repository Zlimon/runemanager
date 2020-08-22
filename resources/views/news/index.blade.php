@extends('layouts.layout')

@section('title')
	{{ __('title.news') }}
@endsection

@section('content')
	<h1>{{ __('title.news') }}</h1>

	@forelse ($newsPosts as $post)
		@if ($loop->first) <div class="card-columns"> @endif
			<div class="card task-box">
				<a href="{{ route('show-newspost', $post->id) }}">
					<img class="card-img-top" src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image">
				</a>
				<div class="card-body">
					<h5 class="card-title"><a href="{{ route('show-newspost', $post->id) }}">{{ $post->title }}</a></h5>
					<p class="card-text">{{ $post->shortstory }}</p>
				</div>
				<div class="card-footer">
					<small><a href="{{ route('show-newspost', $post->id) }}">Read more <i class="fas fa-long-arrow-alt-right"></i></a></small>
				</div>
			</div>
		@if ($loop->last) </div> @endif
	@empty
		<div class="text-center py-5">
			<img class="pixel" src="{{ asset('images') }}/ignore.png" width="75px" alt="Sad face">
			<h1>Nothing interesting is happening</h1>
		</div>
	@endforelse
@endsection