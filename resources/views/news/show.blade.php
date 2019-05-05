@extends('layouts.layout')

@section('title')
	{{ __('title.update-log') }}
@endsection

@section('content')
	<h1>{{ $post->title }}</h1>
	<p class="text-center">{{ $post->shortstory }}</p>
	{!! $post->longstory !!}
@endsection