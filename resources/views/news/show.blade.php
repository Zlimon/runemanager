@extends('layouts.layout')

@section('title')
	{{ $post->title }}
@endsection

@section('content')
	<link href="{{ asset('css/news.css') }}" rel="stylesheet">

	<div class="news-post mb-3">
		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{ route('news') }}">News</a></li>
				<li class="breadcrumb-item"><a href="#">{{ $post->category->category }}</a></li>
				<li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
			</ol>
		</nav>

		<img class="img-fluid rounded mx-auto d-block w-50" src="{{ asset('storage') }}/{{ $post->image->image_file_name }}.{{ $post->image->image_file_extension }}" alt="'{{ $post->title }}' news post image">
		
		<h1>{{ $post->title }}</h1>
		
		<div class="text-center mx-auto w-50">
			<p><em>{{ $post->shortstory }}</em></p>
		</div>
		
		{!! $post->longstory !!}

		<div class="row pt-5">
			<div class="col-6">
				<p class="float-left"><em>- {{ $post->user->name }}</em></p>
			</div>

			<div class="col-6">
				<p class="float-right">{{ $post->created_at }}</p>
			</div>
		</div>
	</div>
@endsection