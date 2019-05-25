@extends('layouts.admin')

@section('title')
	TITLE
@endsection

@section('content')
	@section('navigation')
		<form method="POST" action="{{ route('admin-delete-newspost', $post->id) }}">
			@method('DELETE')
			@csrf

			<button type="submit" class="btn btn-danger">Delete newspost</button>
		</form>
	@endsection

	<div class="content-body">
		<div class="text-center pb-3">
			<img src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image" width="50%">
		</div>
		<h1>{{ $post->title }}</h1>
		<p class="text-center"><em>{{ $post->shortstory }}</em></p>
		{!! $post->longstory !!}
	</div>
@endsection