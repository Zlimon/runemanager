@extends('layouts.layout')

@section('title')
	"{{ $post->title }}"
@endsection

@section('content')
	<div class="text-center pb-3">
		<img src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image" width="50%">
	</div>
	<h1>{{ $post->title }}</h1>
	<p class="text-center"><em>{{ $post->shortstory }}</em></p>
	{!! $post->longstory !!}
@endsection