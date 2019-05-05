@extends('layouts.layout')

@section('title')
	"{{ $post->title }}"
@endsection

@section('content')
	<h1>{{ $post->title }}</h1>
	<p class="text-center">{{ $post->shortstory }}</p>
	{!! $post->longstory !!}
@endsection